<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Invoice as InvoiceEntity;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Repositories\InvoiceRepositoryInterface;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private AuditServiceInterface $auditService,
    ) {}

    public function index(): View
    {
        $invoices = Invoice::with(['contract.quotation.prospect', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Muestra el formulario de creación con contratos activos disponibles.
     */
    public function create(): View
    {
        $contracts = Contract::where('status', ContractStatus::ACTIVE->value)
            ->with('quotation.prospect')
            ->get();
        $initialContracts = $contracts->take(5)->map(fn ($c) => [
            'id' => $c->id,
            'name' => "{$c->contract_number} — {$c->quotation->prospect->company_name}",
            'subtext' => '$' . number_format($c->total_amount, 2),
        ])->values()->toArray();
        return view('invoices.form', compact('contracts', 'initialContracts'));
    }

    /**
     * Almacena una nueva factura.
     * Factura representativa sin validez fiscal electrónica.
     * La factura se crea en estado "issued" y queda asociada al contrato.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0.01',
            'issued_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);
        if ($contract->status !== ContractStatus::ACTIVE->value) {
            return back()->withInput()->with('error', __('domain.invoice.contract_must_be_active'));
        }

        $entity = new InvoiceEntity(
            id: null,
            invoiceNumber: 'INV-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            contractId: $validated['contract_id'],
            amount: $validated['amount'],
            issuedDate: new \DateTimeImmutable($validated['issued_date']),
            status: 'issued',
            notes: $validated['notes'] ?? null,
            createdBy: auth()->id(),
        );

        $this->invoiceRepository->save($entity);
        $this->auditService->logCreate($entity, 'Factura', $validated);

        $invoice = Invoice::latest()->first();

        return to_route('invoices.show', $invoice)
            ->with('success', __('domain.invoice.created'));
    }

    /**
     * Muestra los detalles de una factura con su contrato y prospecto.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['contract.quotation.prospect', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Genera y descarga el PDF de una factura.
     */
    public function pdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $invoice->load(['contract.quotation.prospect', 'createdBy']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download("factura-{$invoice->invoice_number}.pdf");
    }
}
