<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Prospect as ProspectEntity;
use App\Src\Domain\Repositories\ProspectRepositoryInterface;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function __construct(
        private ProspectRepositoryInterface $prospectRepository,
        private AuditServiceInterface $auditService,
    ) {}

    public function index(): View
    {
        $prospects = Prospect::with('createdBy')->orderByDesc('created_at')->paginate(config('kontbox.items_per_page'));
        return view('prospects.index', compact('prospects'));
    }

    public function create(): View
    {
        return view('prospects.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:prospects,email',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:new,contacted,negotiation,won,lost',
            'notes' => 'nullable|string',
        ]);

        $entity = new ProspectEntity(
            id: null,
            companyName: $validated['company_name'],
            contactName: $validated['contact_name'],
            email: $validated['email'],
            phone: $validated['phone'] ?? null,
            status: $validated['status'] ?? 'new',
            notes: $validated['notes'] ?? null,
            createdBy: auth()->id(),
        );

        $this->prospectRepository->save($entity);
        $this->auditService->logCreate($entity, 'Prospecto', $validated);

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
