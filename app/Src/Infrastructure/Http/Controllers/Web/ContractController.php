<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use App\Src\Application\UseCases\Contracts\ActivateContractUseCase;
use App\Src\Application\UseCases\Contracts\AnulateContractUseCase;
use App\Src\Application\UseCases\Contracts\UploadDocumentUseCase;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador para la gestión de contratos con bloqueo de seguridad PDF.
 * Ciclo de vida: Pendiente de documento -> Documento cargado -> Activo -> Cancelado.
 * No se puede activar un contrato sin haber cargado el PDF firmado.
 */
class ContractController extends Controller
{
    /**
     * Lista paginada de contratos con su cotización, prospecto y aprobador.
     */
    public function index(): View
    {
        $contracts = Contract::with(['quotation.prospect', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));
        return view('contracts.index', compact('contracts'));
    }

    /**
     * Muestra el formulario para crear un contrato a partir de una cotización aprobada.
     */
    public function create(Quotation $quotation): View
    {
        if ($quotation->status !== QuotationStatus::APPROVED->value) {
            abort(403, __('domain.contract.quotation_not_approved'));
        }
        if (Contract::where('quotation_id', $quotation->id)->exists()) {
            abort(409, __('domain.contract.quotation_already_contracted'));
        }
        $quotation->load(['items.microservice', 'prospect']);
        return view('contracts.form', compact('quotation'));
    }

    /**
     * Almacena un nuevo contrato en estado "Pendiente de documento"
     * y replica los items de la cotización como servicios del contrato.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);

        if ($quotation->status !== QuotationStatus::APPROVED->value) {
            return back()->withInput()->with('error', __('domain.contract.quotation_not_approved'));
        }

        if (Contract::where('quotation_id', $quotation->id)->exists()) {
            return back()->withInput()->with('error', __('domain.contract.quotation_already_contracted'));
        }

        $contract = Contract::create([
            'contract_number' => 'CTR-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'quotation_id' => $quotation->id,
            'approved_by' => auth()->id(),
            'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => $validated['total_amount'],
        ]);

        foreach ($quotation->items as $item) {
            $contract->services()->create([
                'microservice_id' => $item->microservice_id,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        AuditService::logCreate($contract, 'Contrato', $validated);

        return to_route('contracts.show', $contract)
            ->with('success', __('domain.contract.created'));
    }

    /**
     * Muestra los detalles completos del contrato con servicios y anexos.
     */
    public function show(Contract $contract): View
    {
        $contract->load([
            'quotation.prospect',
            'approvedBy',
            'services.microservice',
            'amendments',
        ]);
        return view('contracts.show', compact('contract'));
    }

    /**
     * Bloqueo de seguridad: el PDF firmado es obligatorio.
     * Carga el PDF firmado y cambia el estado a "Documento cargado".
     * El documento se almacena en storage/contracts/{id}/.
     */
    public function uploadDocument(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf|max:' . config('kontbox.max_pdf_size_kb'),
        ]);

        $path = $validated['signed_pdf']->store('contracts/' . $contract->id);

        try {
            app(UploadDocumentUseCase::class)->execute(
                $contract->id,
                $path,
                $validated['signed_pdf']->getClientOriginalName(),
                $validated['signed_pdf']->getSize()
            );

            return back()->with('success', __('domain.contract.document_uploaded'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
        }
    }

    /**
     * Bloqueo de seguridad: no se activa sin PDF cargado.
     * Activa el contrato cambiando su estado a "Activo".
     * Verifica internamente que el PDF firmado ya fue cargado.
     * Al activar, crea automáticamente las ActivityInstances para cada
     * actividad de los microservicios incluidos en el contrato.
     */
    public function activate(int $id): RedirectResponse
    {
        try {
            app(ActivateContractUseCase::class)->execute($id);

            return back()->with('success', __('domain.contract.activated_successfully'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
        }
    }

    /**
     * Anulación operativa: libera recursos para nuevas ventas.
     * Cancela un contrato registrando el motivo obligatorio.
     */
    public function anulate(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        try {
            app(AnulateContractUseCase::class)->execute($contract->id, $validated['reason']);
            return back()->with('success', __('domain.contract.anulated'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
        }
    }
}
