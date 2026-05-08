<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador para la gestión de facturas representativas (sin validez fiscal).
 * Las facturas generadas aquí son documentos informativos/comerciales
 * basados en contratos activos, sin validez fiscal electrónica (no son CFDI).
 */
class InvoiceController extends Controller
{
    /**
     * Lista paginada de facturas con su contrato, prospecto y creador.
     */
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

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'contract_id' => $validated['contract_id'],
            'amount' => $validated['amount'],
            'issued_date' => $validated['issued_date'],
            'status' => 'issued',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditService::logCreate($invoice, 'Factura', $validated);

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
