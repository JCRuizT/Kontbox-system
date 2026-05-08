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
                'custom_price' => $s->custom_price ?? $s->microservice->base_cost,
                'activities' => $s->microservice->activities
                    ? $s->microservice->activities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'essential' => $a->is_essential])->toArray()
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
            'plan_id' => 'required|exists:plans,id',
            'valid_until' => 'nullable|date|after:today',
            'selected_items' => 'nullable|json',
            'items' => 'nullable|array|min:1',
            'items.*.microservice_id' => 'required_with:items|exists:microservices,id',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        $items = [];
        $excludedActivities = [];
        if ($request->filled('selected_items')) {
            $decoded = json_decode($validated['selected_items'], true);
            foreach ($decoded as $it) {
                $items[] = [
                    'microservice_id' => $it['microservice_id'],
                    'unit_price' => $it['unit_price'],
                ];
                $excludedActivities[$it['microservice_id']] = $it['excluded_activities'] ?? [];
            }
        } elseif ($request->has('items')) {
            $items = $validated['items'];
        }

        if (empty($items)) {
            return back()->withInput()->with('error', __('domain.quotation.at_least_one_service'));
        }

        // Custom plan logic: compare items with base plan services
        $plan = Plan::with('services')->find($validated['plan_id']);
        $customPlanId = $plan->id;

        if ($plan) {
            $planServices = $plan->services->map(fn ($s) => [
                'microservice_id' => $s->microservice_id,
                'unit_price' => $s->custom_price ?? $s->microservice->base_cost,
            ])->toArray();

            // Normalize for comparison (sort by microservice_id)
            $sortItems = fn ($arr) => collect($arr)->sortBy('microservice_id')->values()->toArray();
            $planNormalized = $sortItems($planServices);
            $itemsNormalized = $sortItems($items);

            if ($planNormalized !== $itemsNormalized) {
                // Create custom plan based on the original
                $customPlan = Plan::create([
                    'name' => $plan->name . ' (Personalizado)',
                    'description' => $plan->description,
                    'is_active' => true,
                    'is_custom' => true,
                    'parent_plan_id' => $plan->id,
                ]);
                foreach ($items as $it) {
                    $customPlan->services()->create([
                        'microservice_id' => $it['microservice_id'],
                        'custom_price' => $it['unit_price'],
                    ]);
                }
                $customPlanId = $customPlan->id;

                AuditService::logBusiness(
                    "Creó plan personalizado #{$customPlan->id} a partir del plan base #{$plan->id} para cotización del prospecto #{$validated['prospect_id']}"
                );
            }
        }

        $pricing = app(\App\Src\Application\Services\QuotationPricingService::class)->calculate($items);
        $subtotal = $pricing['subtotal'];
        $tax = $pricing['tax'];
        $total = $pricing['total'];

        $quotation = Quotation::create([
            'quote_number' => 'COT-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'prospect_id' => $validated['prospect_id'],
            'plan_id' => $customPlanId,
            'created_by' => auth()->id(),
            'status' => QuotationStatus::DRAFT->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'valid_until' => $validated['valid_until'] ?? now()->addDays(config('kontbox.quotation_valid_days')),
            'version' => 1,
        ]);

        foreach ($items as $item) {
            $ms = Microservice::with('activities')->find($item['microservice_id']);
            $quotation->items()->create([
                'microservice_id' => $item['microservice_id'],
                'service_name_snapshot' => $ms->name,
                'description_snapshot' => $ms->description,
                'unit_price' => $item['unit_price'],
                'total_price' => $item['unit_price'],
                'excluded_activities' => $excludedActivities[$item['microservice_id']] ?? [],
            ]);
        }

        $logData = array_merge($validated, ['custom_plan' => $customPlanId !== $plan->id, 'excluded_activities' => $excludedActivities]);
        AuditService::logCreate($quotation, 'Cotización', $logData);

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
        try {
            app(SendQuotationForApprovalUseCase::class)->execute($id);
            return back()->with('success', __('domain.quotation.sent_for_approval'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
        }
    }

    public function approve(int $id): RedirectResponse
    {
        try {
            app(ApproveQuotationUseCase::class)->execute($id);
            return back()->with('success', __('domain.quotation.approved'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
        }
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        try {
            app(RejectQuotationUseCase::class)->execute($id, $validated['rejection_reason']);
            return back()->with('success', __('domain.quotation.rejected'));
        } catch (\DomainException $e) {
            return back()->with('error', __($e->getMessage()));
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
