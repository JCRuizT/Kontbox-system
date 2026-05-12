<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Activity as ActivityEntity;
use App\Src\Domain\Repositories\ActivityRepositoryInterface;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Activity;
use App\Src\Infrastructure\Persistence\Models\ActivityInstance;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        private ActivityRepositoryInterface $activityRepository,
        private AuditServiceInterface $auditService,
    ) {}

    public function index(): View
    {
        $microservices = Microservice::with(['activities' => function ($q) {
            $q->orderBy('is_essential', 'desc')->orderBy('name');
        }])->whereHas('activities')->orDoesntHave('activities')->orderBy('name')->get();

        return view('activities.index', compact('microservices'));
    }

    public function create(): View
    {
        return view('activities.form', [
            'microservices' => Microservice::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'microservice_id' => 'required|exists:microservices,id,is_active,1',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_essential' => 'nullable|boolean',
        ]);

        $entity = new ActivityEntity(
            id: null,
            microserviceId: $validated['microservice_id'],
            name: $validated['name'],
            description: $validated['description'] ?? null,
            isActive: true,
            isEssential: $request->boolean('is_essential'),
        );

        $this->activityRepository->save($entity);
        $this->auditService->logCreate($entity, 'Actividad', $validated);

        return to_route('activities.index')
            ->with('success', __('domain.activity.created'));
    }

    public function edit(Activity $activity): View
    {
        return view('activities.form', [
            'activity' => $activity,
            'microservices' => Microservice::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_essential' => 'nullable|boolean',
        ]);

        $validated['is_essential'] = $request->boolean('is_essential');

        // microservice_id es inmutable después de la creación
        unset($validated['microservice_id']);

        // Si cambia is_essential, requiere permiso específico
        if ($activity->is_essential !== $validated['is_essential']) {
            if (!$request->user()->can('activities.essential')) {
                return back()->with('error', __('domain.activity.essential_permission_required'));
            }
        }

        $original = $activity->getOriginal();
        $activity->update($validated);
        $changes = $activity->getChanges();

        AuditService::logUpdate($activity, 'Actividad', $original, $changes);

        if (array_key_exists('is_essential', $changes)) {
            AuditService::logBusiness(
                ($changes['is_essential'] ? 'Marcó' : 'Desmarcó') . " como esencial la Actividad #{$activity->id}: {$activity->name}"
            );
        }

        return to_route('activities.index')
            ->with('success', __('domain.activity.updated'));
    }

    /**
     * Elimina físicamente si la actividad no tiene instancias en contratos.
     * Si tiene relaciones, aplica baja lógica (desactiva).
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        if ($activity->is_essential) {
            return back()->with('error', __('domain.activity.essential_cannot_deactivate'));
        }

        // Verificar si tiene instancias en contratos
        $instancesCount = ActivityInstance::where('activity_id', $activity->id)->count();

        if ($instancesCount > 0) {
            // Tiene relaciones: solo baja lógica
            $activity->update(['is_active' => false]);
            AuditService::logDelete($activity, 'Actividad');
            return to_route('activities.index')
                ->with('warning', __('domain.activity.deactivated_with_relations', ['count' => $instancesCount]));
        }

        // Sin relaciones: eliminación física definitiva
        $msName = $activity->microservice->name ?? '-';
        AuditService::log("Eliminó físicamente Actividad #{$activity->id}: {$activity->name} del microservicio {$msName}", $activity, ['action' => 'force_delete'], AuditService::CRUD);
        $activity->delete();

        return to_route('activities.index')
            ->with('success', __('domain.activity.permanently_deleted'));
    }

    /**
     * Reactiva una actividad previamente desactivada.
     */
    public function activate(Activity $activity): RedirectResponse
    {
        if (!$activity->microservice || !$activity->microservice->is_active) {
            return back()->with('error', __('domain.activity.cannot_activate_without_microservice'));
        }

        $activity->update(['is_active' => true]);

        AuditService::log('Reactivated activity', $activity, ['action' => 'activate', 'activity_id' => $activity->id], AuditService::CRUD);

        return to_route('activities.index')
            ->with('success', __('domain.activity.activated'));
    }
}
