<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
    /**
     * Lista paginada de todos los microservicios.
     */
    public function index(): View
    {
        $microservices = Microservice::paginate(config('kontbox.items_per_page'));
        return view('microservices.index', compact('microservices'));
    }

    /**
     * Muestra el formulario para crear un nuevo microservicio.
     */
    public function create(): View
    {
        return view('microservices.form');
    }

    /**
     * Almacena un nuevo microservicio y registra la auditoría de creación.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,one_time',
        ]);

        $ms = Microservice::create($validated);

        AuditService::logCreate($ms, 'Microservicio', $validated);

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.created'));
    }

    /**
     * Muestra el formulario para editar un microservicio existente.
     */
    public function edit(Microservice $microservice): View
    {
        return view('microservices.form', compact('microservice'));
    }

    /**
     * Actualiza un microservicio y registra los cambios en auditoría.
     */
    public function update(Request $request, Microservice $microservice): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,one_time',
        ]);

        $original = $microservice->getOriginal();
        $microservice->update($validated);
        $changes = $microservice->getChanges();
        AuditService::logUpdate($microservice, 'Microservicio', $original, $changes);

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.updated'));
    }

    /**
     * Desactiva (soft delete) un microservicio y registra la auditoría de baja.
     * No elimina físicamente el registro para mantener integridad referencial.
     */
    public function destroy(Microservice $microservice): RedirectResponse
    {
        AuditService::logDelete($microservice, 'Microservicio');
        $microservice->update(['is_active' => false]);

        return to_route('microservices.index')
            ->with('success', __('domain.microservice.deactivated'));
    }
}
