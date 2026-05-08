<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
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
        $plans = Plan::with('services.microservice')->paginate(config('kontbox.items_per_page'));
        return view('plans.index', compact('plans'));
    }

    /**
     * Muestra el formulario de creación con la lista de microservicios activos.
     */
    public function create(): View
    {
        return view('plans.form', [
            'microservices' => Microservice::where('is_active', true)->get(),
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
            'services' => 'required|array|min:1',
            'services.*.microservice_id' => 'required|exists:microservices,id,is_active,1',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        $plan = Plan::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['services'] as $service) {
            $plan->services()->create([
                'microservice_id' => $service['microservice_id'],
                'quantity' => $service['quantity'],
                'custom_price' => $service['custom_price'] ?? null,
            ]);
        }

        AuditService::logCreate($plan, 'Plan', $validated);

        return to_route('plans.index')
            ->with('success', __('domain.plan.created'));
    }

    /**
     * Muestra el formulario de edición con los servicios cargados del plan.
     */
    public function edit(Plan $plan): View
    {
        $plan->load('services');
        return view('plans.form', [
            'plan' => $plan,
            'microservices' => Microservice::where('is_active', true)->get(),
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
            'services' => 'required|array|min:1',
            'services.*.microservice_id' => 'required|exists:microservices,id,is_active,1',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        $original = $plan->getOriginal();
        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $plan->services()->delete();
        foreach ($validated['services'] as $service) {
            $plan->services()->create([
                'microservice_id' => $service['microservice_id'],
                'quantity' => $service['quantity'],
                'custom_price' => $service['custom_price'] ?? null,
            ]);
        }

        AuditService::logUpdate($plan, 'Plan', $original, $plan->getChanges());

        return to_route('plans.index')
            ->with('success', __('domain.plan.updated'));
    }

    /**
     * Desactiva un plan (baja lógica) y registra auditoría.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        AuditService::logDelete($plan, 'Plan');
        $plan->update(['is_active' => false]);
        return to_route('plans.index')
            ->with('success', __('domain.plan.deactivated'));
    }
}
