<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Infrastructure\Persistence\Models\Activity;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\PlanActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador para la gestión de planes comerciales.
 * Los planes agrupan microservicios con cantidades y precios personalizados
 * que se ofrecen a los prospectos como paquetes prediseñados.
 */
class PlanController extends Controller
{
    /**
     * Lista paginada de planes con sus servicios y microservicios asociados.
     */
    public function index(): View
    {
        $plans = Plan::with(['services.microservice', 'planActivities.activity'])
            ->paginate(config('kontbox.items_per_page'));
        return view('plans.index', compact('plans'));
    }

    /**
     * Muestra el formulario de creación con la lista de microservicios activos.
     */
    public function create(): View
    {
        return view('plans.form', [
            'microservices' => Microservice::where('is_active', true)
                ->with('activities')
                ->get(),
        ]);
    }

    /**
     * Almacena un nuevo plan con sus servicios asociados y registra auditoría.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'services_data' => 'required|string',
        ]);

        $servicesData = $this->parseAndValidateServices($validated['services_data']);

        $plan = Plan::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->savePlanServices($plan, $servicesData);

        AuditService::logCreate($plan, 'Plan', $validated);

        return to_route('plans.index')
            ->with('success', __('domain.plan.created'));
    }

    /**
     * Muestra el formulario de edición con los servicios cargados del plan.
     */
    public function edit(Plan $plan): View
    {
        $plan->load(['services.microservice', 'planActivities.activity']);
        return view('plans.form', [
            'plan' => $plan,
            'microservices' => Microservice::where('is_active', true)
                ->with('activities')
                ->get(),
        ]);
    }

    /**
     * Actualiza un plan: reemplaza todos los servicios (borra y recrea)
     * y registra los cambios en auditoría.
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'services_data' => 'required|string',
        ]);

        $servicesData = $this->parseAndValidateServices($validated['services_data']);

        $original = $plan->getOriginal();
        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $plan->services()->delete();
        $this->savePlanServices($plan, $servicesData);

        AuditService::logUpdate($plan, 'Plan', $original, $plan->getChanges());

        return to_route('plans.index')
            ->with('success', __('domain.plan.updated'));
    }

    /**
     * Desactiva un plan (baja lógica) y registra auditoría.
     * Un plan desactivado no aparece en nuevas cotizaciones,
     * pero no afecta cotizaciones o contratos existentes (usan snapshots).
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => false]);

        AuditService::logDelete($plan, 'Plan');

        return to_route('plans.index')
            ->with('success', __('domain.plan.deactivated'));
    }

    /**
     * Reactiva un plan previamente desactivado.
     * Vuelve a estar disponible para nuevas cotizaciones.
     */
    public function activate(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => true]);

        AuditService::log('Reactivated plan', $plan, ['action' => 'activate', 'plan_id' => $plan->id], AuditService::CRUD);

        return to_route('plans.index')
            ->with('success', __('domain.plan.activated'));
    }

    /**
     * Habilita o deshabilita una actividad dentro del plan.
     * Las actividades esenciales (is_essential) no pueden deshabilitarse.
     */
    public function toggleActivity(Request $request, Plan $plan, Activity $activity): RedirectResponse
    {
        $planActivity = $plan->planActivities()->where('activity_id', $activity->id)->first();

        if (!$planActivity) {
            return back()->with('error', __('domain.plan.activity_not_in_plan'));
        }

        if ($activity->is_essential && $planActivity->is_enabled) {
            return back()->with('error', __('domain.activity.essential_cannot_deactivate'));
        }

        $previousState = $planActivity->is_enabled;
        $planActivity->update(['is_enabled' => !$planActivity->is_enabled]);

        $action = $planActivity->is_enabled ? 'enabled' : 'disabled';
        AuditService::log(
            "Activity {$action} in plan",
            $planActivity,
            [
                'action' => $action,
                'plan_id' => $plan->id,
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'previous_state' => $previousState,
            ],
            AuditService::CRUD
        );

        $messageKey = $planActivity->is_enabled ? 'domain.plan.activity_enabled' : 'domain.plan.activity_disabled';

        return back()->with('success', __($messageKey));
    }

    /**
     * Parsea y valida el JSON de servicios enviado desde el formulario.
     * Cada servicio debe tener microservice_id, unit_price y excluded_activities opcional.
     */
    private function parseAndValidateServices(string $servicesJson): array
    {
        $services = json_decode($servicesJson, true);

        if (!is_array($services) || empty($services)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], ['services_data' => 'required|array|min:1'])->errors()
            );
        }

        $microserviceIds = array_column($services, 'microservice_id');
        $existingIds = Microservice::whereIn('id', $microserviceIds)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        foreach ($microserviceIds as $msId) {
            if (!in_array($msId, $existingIds)) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], ['services_data' => "Microservicio ID {$msId} no existe o está inactivo"])->errors()
                );
            }
        }

        return $services;
    }

    /**
     * Guarda los servicios del plan y sincroniza las actividades excluidas.
     */
    private function savePlanServices(Plan $plan, array $servicesData): void
    {
        foreach ($servicesData as $svc) {
            $plan->services()->create([
                'microservice_id' => $svc['microservice_id'],
                'custom_price' => $svc['unit_price'] ?? null,
            ]);
        }

        $plan->syncActivities();

        // Procesar actividades excluidas (deshabilitadas)
        $excludedByUser = collect($servicesData)
            ->filter(fn ($svc) => !empty($svc['excluded_activities']))
            ->flatMap(fn ($svc) => $svc['excluded_activities'])
            ->unique()
            ->toArray();

        if (!empty($excludedByUser)) {
            $plan->planActivities()
                ->whereIn('activity_id', $excludedByUser)
                ->update(['is_enabled' => false]);
        }
    }
}
