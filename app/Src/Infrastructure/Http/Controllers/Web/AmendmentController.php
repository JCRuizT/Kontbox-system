<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\ContractAmendment as AmendmentEntity;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Repositories\AmendmentRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador para la gestión de anexos/adiciones (modificaciones) a contratos activos.
 * Los anexos permiten modificar términos de un contrato ya activo,
 * siempre que se acompañen de un PDF firmado que respalde el cambio.
 */
class AmendmentController extends Controller
{
    public function __construct(
        private AmendmentRepositoryInterface $amendmentRepository,
        private AuditServiceInterface $auditService,
    ) {}

    public function index(): View
    {
        $amendments = ContractAmendment::with(['contract.quotation.prospect', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));
        return view('amendments.index', compact('amendments'));
    }

    /**
     * Muestra el formulario para crear un anexo sobre un contrato activo.
     * Bloqueo de seguridad: solo contratos en estado "Activo" pueden tener anexos.
     */
    public function create(Contract $contract): View
    {
        if ($contract->status !== ContractStatus::ACTIVE->value) {
            abort(403, __('domain.amendment.only_active_contracts'));
        }
        $contract->load([
            'quotation.prospect',
            'services.microservice.activities',
            'activityInstances.activity',
        ]);
        return view('amendments.form', compact('contract'));
    }

    /**
     * Almacena un nuevo anexo con PDF firmado obligatorio.
     * Regla de seguridad: el PDF firmado es obligatorio para procesar modificación.
     * Solo se permiten anexos en contratos con estado "Activo".
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'description' => 'required|string|min:10',
            'signed_pdf' => 'required|file|mimes:pdf|max:' . config('kontbox.max_pdf_size_kb'),
            'modified_services' => 'nullable|json',
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);

        if ($contract->status !== ContractStatus::ACTIVE->value) {
            return back()->with('error', __('domain.amendment.only_active_contracts'));
        }

        $pdfPath = $validated['signed_pdf']->store('amendments/' . $contract->id);

        // Procesar cambios de servicios y actividades si se enviaron
        $modifiedServices = $validated['modified_services'] ?? null;
        if ($modifiedServices) {
            $parsed = json_decode($modifiedServices, true);
            if (is_array($parsed)) {
                // Procesar cambios en servicios (microservicios del contrato)
                if (isset($parsed['services'])) {
                    $contract->load('services.microservice.activities');
                    foreach ($parsed['services'] as $key => $enabled) {
                        if (str_starts_with($key, 'svc_')) {
                            $serviceId = substr($key, 4);
                            // No permitir deshabilitar un servicio que tenga actividades esenciales
                            if (!$enabled) {
                                $svc = $contract->services->firstWhere('id', $serviceId);
                                if ($svc && $svc->microservice) {
                                    $hasEssential = $svc->microservice->activities->contains('is_essential', true);
                                    if ($hasEssential) {
                                        continue;
                                    }
                                }
                            }
                            $contract->services()->where('id', $serviceId)->update(['is_enabled' => $enabled]);
                        }
                    }
                }
                // Procesar cambios en actividades
                if (isset($parsed['activities'])) {
                    foreach ($parsed['activities'] as $activityId => $enabled) {
                        $instance = $contract->activityInstances()
                            ->where('activity_id', $activityId)
                            ->first();
                        if ($instance) {
                            $activity = $instance->activity;
                            if ($activity && $activity->is_essential && !$enabled) {
                                continue;
                            }
                            $instance->update(['is_enabled' => $enabled]);
                        }
                    }
                }
            }
        }

        $entity = new AmendmentEntity(
            id: null,
            contractId: $contract->id,
            amendmentNumber: 'OTR-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            description: $validated['description'],
            signedPdfPath: $pdfPath,
            modifiedServices: $validated['modified_services'] ? json_decode($validated['modified_services'], true) : null,
            createdBy: auth()->id(),
        );

        $this->amendmentRepository->save($entity);
        $this->auditService->logCreate($entity, 'Anexo', $validated);

        return to_route('contracts.show', $contract)
            ->with('success', __('domain.amendment.created'));
    }

    /**
     * Muestra los detalles de un anexo específico.
     */
    public function show(ContractAmendment $amendment): View
    {
        $amendment->load(['contract.quotation.prospect', 'createdBy']);
        return view('amendments.show', compact('amendment'));
    }
}
