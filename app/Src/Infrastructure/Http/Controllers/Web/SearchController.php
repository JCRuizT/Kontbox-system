<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use Illuminate\Http\Request;

/**
 * Controlador de búsqueda AJAX para selects con autocompletado.
 * Provee endpoints JSON para buscar prospectos, planes, microservicios,
 * contratos y usuarios utilizados en formularios del sistema.
 */
class SearchController extends Controller
{
    /**
     * Determina el límite de resultados: 20 si hay query, 5 si está vacío.
     */
    private function limit(Request $request): int
    {
        return $request->filled('q') && strlen(trim($request->get('q'))) > 0 ? 20 : 5;
    }

    /**
     * Busca prospectos por nombre de empresa, contacto o email.
     */
    public function prospects(Request $request): array
    {
        $query = $request->get('q', '');
        return Prospect::when($query, fn ($q) => $q->where(function ($b) use ($query) {
                $b->where('company_name', 'like', "%{$query}%")
                  ->orWhere('contact_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            }))
            ->orderBy('company_name')
            ->limit($this->limit($request))
            ->get(['id', 'company_name as name', 'contact_name as subtext', 'email'])
            ->toArray();
    }

    /**
     * Busca planes activos por nombre o descripción.
     */
    public function plans(Request $request): array
    {
        $query = $request->get('q', '');
        return Plan::where('is_active', true)
            ->when($query, fn ($q) => $q->where(function ($b) use ($query) {
                $b->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            }))
            ->orderBy('name')
            ->limit($this->limit($request))
            ->get(['id', 'name', 'description as subtext'])
            ->toArray();
    }

    /**
     * Busca microservicios activos por nombre o descripción, incluyendo su actividad.
     */
    public function microservices(Request $request): array
    {
        $query = $request->get('q', '');
        return Microservice::where('is_active', true)
            ->when($query, fn ($q) => $q->where(function ($b) use ($query) {
                $b->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            }))
            ->with('activities')
            ->orderBy('name')
            ->limit($this->limit($request))
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'subtext' => $m->activities->first()?->name ?? null,
                'base_cost' => $m->base_cost,
            ])
            ->toArray();
    }

    /**
     * Busca contratos activos por número o nombre del prospecto asociado.
     */
    public function contracts(Request $request): array
    {
        $query = $request->get('q', '');
        return Contract::where('status', 'active')
            ->when($query, fn ($q) => $q->where(function ($b) use ($query) {
                $b->whereHas('quotation.prospect', fn ($p) => $p->where('company_name', 'like', "%{$query}%"))
                  ->orWhere('contract_number', 'like', "%{$query}%");
            }))
            ->with('quotation.prospect')
            ->orderByDesc('created_at')
            ->limit($this->limit($request))
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => "{$c->contract_number} — {$c->quotation->prospect->company_name}",
                'subtext' => '$' . number_format($c->total_amount, 2),
            ])
            ->toArray();
    }

    /**
     * Busca usuarios del sistema por nombre o email.
     */
    public function users(Request $request): array
    {
        $query = $request->get('q', '');
        return User::when($query, fn ($q) => $q->where(function ($b) use ($query) {
                $b->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            }))
            ->orderBy('name')
            ->limit($this->limit($request))
            ->get(['id', 'name', 'email as subtext'])
            ->toArray();
    }
}
