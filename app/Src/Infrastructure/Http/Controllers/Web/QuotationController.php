<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Application\UseCases\Quotations\ApproveQuotationUseCase;
use App\Src\Application\UseCases\Quotations\RejectQuotationUseCase;
use App\Src\Application\UseCases\Quotations\SendQuotationForApprovalUseCase;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador para la gestión de cotizaciones con inmutabilidad.
 * Las cotizaciones siguen un ciclo de vida: Borrador -> En revisión -> Aprobada/Rechazada.
 * Una vez enviadas a aprobación, los datos no pueden modificarse.
 * Solo las cotizaciones rechazadas pueden generar una nueva versión.
 */
class QuotationController extends Controller
{
    /**
     * Lista paginada de cotizaciones con su prospecto y creador.
     */
    public function index(): View
    {
        $quotations = Quotation::with(['prospect', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));
        return view('quotations.index', compact('quotations'));
    }

    /**
     * Muestra el formulario de cotización con planes, prospectos y microservicios
     * precargados para la selección.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)
            ->with('services.microservice.activities')
            ->get();

        $plansData = $plans->mapWithKeys(fn ($plan) => [
            $plan->id => $plan->services->map(fn ($s) => [
                'id' => $s->microservice_id,
                'name' => $s->microservice->name,
                'base_cost' => $s->microservice->base_cost,
                'quantity' => $s->quantity,
                'custom_price' => $s->custom_price ?? $s->microservice->base_cost,
                'activities' => $s->microservice->activities
                    ? $s->microservice->activities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->toArray()
                    : [],
            ])->toArray(),
        ])->toArray();

        return view('quotations.form', [
            'initialProspects' => Prospect::orderBy('company_name')->limit(5)->get(['id', 'company_name', 'contact_name'])->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->company_name,
                'subtext' => $p->contact_name,
            ])->toArray(),
            'initialPlans' => Plan::where('is_active', true)->orderBy('name')->limit(5)->get(['id', 'name', 'description'])->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'subtext' => $p->description ?? '',
            ])->toArray(),
            'plans' => $plans,
            'plansDataJson' => json_encode($plansData),
            'allMicroservices' => Microservice::where('is_active', true)
                ->with('activities')
                ->get(),
        ]);
    }

    /**
     * Almacena una nueva cotización en estado Borrador (draft).
     * Regla de auditoría: la cotización se crea en estado Borrador (draft).
     * Calcula subtotal, impuesto y total automáticamente.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prospect_id' => 'required|exists:prospects,id',
            'plan_id' => 'nullable|exists:plans,id',
            'valid_until' => 'nullable|date|after:today',
            'selected_items' => 'nullable|json',
            'items' => 'nullable|array|min:1',
            'items.*.microservice_id' => 'required_with:items|exists:microservices,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        // Parse items from JSON (new form) or array (legacy)
        $items = [];
        if ($request->filled('selected_items')) {
            $items = json_decode($validated['selected_items'], true);
        } elseif ($request->has('items')) {
            $items = $validated['items'];
        }

        if (empty($items)) {
            return back()->withInput()->with('error', __('domain.quotation.at_least_one_service'));
        }

        $subtotal = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
        $tax = $subtotal * config('kontbox.tax_rate');
        $total = $subtotal + $tax;

        // Regla de auditoría: la cotización se crea en estado Borrador (draft)
        $quotation = Quotation::create([
            'quote_number' => 'COT-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'prospect_id' => $validated['prospect_id'],
            'plan_id' => $validated['plan_id'] ?? null,
            'created_by' => auth()->id(),
            'status' => QuotationStatus::DRAFT->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'valid_until' => $validated['valid_until'] ?? now()->addDays(config('kontbox.quotation_valid_days')),
            'version' => 1,
        ]);

        foreach ($items as $item) {
            $ms = Microservice::find($item['microservice_id']);
            $quotation->items()->create([
                'microservice_id' => $item['microservice_id'],
                'service_name_snapshot' => $ms->name,
                'description_snapshot' => $ms->description,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        AuditService::logCreate($quotation, 'Cotización', $validated);

        return to_route('quotations.index')
            ->with('success', __('domain.quotation.created'));
    }

    /**
     * Muestra los detalles completos de una cotización.
     */
    public function show(Quotation $quotation): View
    {
        $quotation->load(['prospect', 'createdBy', 'items.microservice']);
        return view('quotations.show', compact('quotation'));
    }

    /**
     * Cambia estado a En revisión. Una vez enviada, es inmutable.
     */
    public function sendForApproval(int $id): RedirectResponse
    {
        $quotation = Quotation::findOrFail($id);
        try {
            app(SendQuotationForApprovalUseCase::class)->execute($id);
            AuditService::logStatusChange($quotation->fresh(), 'Cotización', QuotationStatus::DRAFT->value, QuotationStatus::UNDER_REVIEW->value);
            return back()->with('success', __('domain.quotation.sent_for_approval'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Solo la gerencia comercial puede aprobar/rechazar.
     * Aprueba una cotización y la deja lista para generar contrato.
     */
    public function approve(int $id): RedirectResponse
    {
        $quotation = Quotation::findOrFail($id);
        try {
            app(ApproveQuotationUseCase::class)->execute($id);
            AuditService::logStatusChange($quotation->fresh(), 'Cotización', QuotationStatus::UNDER_REVIEW->value, QuotationStatus::APPROVED->value);
            return back()->with('success', __('domain.quotation.approved'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Solo la gerencia comercial puede aprobar/rechazar.
     * Rechaza una cotización con un motivo obligatorio (mín. 10 caracteres).
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $quotation = Quotation::findOrFail($id);
        try {
            app(RejectQuotationUseCase::class)->execute($id, $validated['rejection_reason']);
            AuditService::logStatusChange($quotation->fresh(), 'Cotización', QuotationStatus::UNDER_REVIEW->value, QuotationStatus::REJECTED->value);
            return back()->with('success', __('domain.quotation.rejected'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Crea una nueva versión a partir de una cotización rechazada.
     * Solo disponible cuando el estado es "rechazada".
     * La nueva versión incrementa el número de versión y vuelve a Borrador.
     */
    public function newVersion(Quotation $quotation): RedirectResponse
    {
        if ($quotation->status !== QuotationStatus::REJECTED->value) {
            return back()->with('error', __('domain.quotation.immutable'));
        }

        $newVersion = $quotation->replicate();
        $newVersion->quote_number = 'COT-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
        $newVersion->version = $quotation->version + 1;
        $newVersion->parent_id = $quotation->id;
        $newVersion->status = QuotationStatus::DRAFT->value;
        $newVersion->rejection_reason = null;
        $newVersion->save();

        foreach ($quotation->items as $item) {
            $newVersion->items()->create($item->toArray());
        }

        AuditService::logCreate($newVersion, 'Cotización (nueva versión)', ['parent_id' => $quotation->id, 'version' => $newVersion->version]);

        return redirect()->route('quotations.show', $newVersion)
            ->with('success', __('domain.quotation.new_version_created'));
    }
}
