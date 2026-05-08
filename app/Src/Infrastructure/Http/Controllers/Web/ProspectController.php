<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador para la gestión de prospectos (clientes potenciales).
 * Administra la creación, actualización y visualización de prospectos
 * que luego pueden convertirse en cotizaciones y contratos.
 */
class ProspectController extends Controller
{
    /**
     * Lista paginada de prospectos con su creador.
     */
    public function index(): View
    {
        $prospects = Prospect::with('createdBy')->paginate(config('kontbox.items_per_page'));
        return view('prospects.index', compact('prospects'));
    }

    /**
     * Muestra el formulario para crear un nuevo prospecto.
     */
    public function create(): View
    {
        return view('prospects.form');
    }

    /**
     * Almacena un nuevo prospecto asignando el usuario autenticado como creador.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:prospects,email',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $prospect = Prospect::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        AuditService::logCreate($prospect, 'Prospecto', $validated);

        return to_route('prospects.index')
            ->with('success', __('domain.prospect.created'));
    }

    /**
     * Muestra los detalles de un prospecto con su creador.
     */
    public function show(Prospect $prospect): View
    {
        $prospect->load('createdBy');
        return view('prospects.show', compact('prospect'));
    }

    /**
     * Muestra el formulario para editar un prospecto existente.
     */
    public function edit(Prospect $prospect): View
    {
        return view('prospects.form', compact('prospect'));
    }

    /**
     * Actualiza los datos del prospecto incluyendo el estado del pipeline de ventas.
     */
    public function update(Request $request, Prospect $prospect): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:prospects,email,' . $prospect->id,
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:new,contacted,negotiation,won,lost',
            'notes' => 'nullable|string',
        ]);

        $original = $prospect->getOriginal();
        $prospect->update($validated);
        $changes = $prospect->getChanges();

        AuditService::logUpdate($prospect, 'Prospecto', $original, $changes);

        return to_route('prospects.index')
            ->with('success', __('domain.prospect.updated'));
    }
}
