<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Microservice as MicroserviceEntity;
use App\Src\Domain\Repositories\MicroserviceRepositoryInterface;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador para la gestión de microservicios del catálogo.
 * Administra el CRUD de servicios técnicos (recurring/one_time)
 * que componen los planes y contratos del sistema.
 */
class MicroserviceController extends Controller
{
    public function __construct(
        private MicroserviceRepositoryInterface $microserviceRepository,
        private AuditServiceInterface $auditService,
    ) {}

    public function index(): View
    {
        $microservices = Microservice::paginate(config('kontbox.items_per_page'));
        return view('microservices.index', compact('microservices'));
    }

    public function create(): View
    {
        return view('microservices.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,one_time',
        ]);

        $entity = new MicroserviceEntity(
            id: null,
            name: $validated['name'],
            description: $validated['description'] ?? null,
            baseCost: $validated['base_cost'],
            type: $validated['type'],
            isActive: true,
        );

        $this->microserviceRepository->save($entity);

        $this->auditService->logCreate($entity, 'Microservicio', $validated);

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.created'));
    }

    public function edit(Microservice $microservice): View
    {
        return view('microservices.form', compact('microservice'));
    }

    public function update(Request $request, Microservice $microservice): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,one_time',
        ]);

        $entity = $this->microserviceRepository->findById($microservice->id);
        if (!$entity) {
            return back()->with('error', __('domain.microservice.not_found'));
        }

        $original = $microservice->getOriginal();
        $microservice->update($validated);
        AuditService::logUpdate($microservice, 'Microservicio', $original, $microservice->getChanges());

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.updated'));
    }

    public function destroy(Microservice $microservice): RedirectResponse
    {
        $microservice->update(['is_active' => false]);
        AuditService::logDelete($microservice, 'Microservicio');

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.deactivated'));
    }

    public function activate(Microservice $microservice): RedirectResponse
    {
        $microservice->update(['is_active' => true]);
        AuditService::log('Reactivated microservice', $microservice, ['action' => 'activate', 'microservice_id' => $microservice->id], AuditService::CRUD);

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.activated'));
    }
}
