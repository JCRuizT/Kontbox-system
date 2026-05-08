<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador para el panel de revisión de la gerencia comercial.
 * Muestra cotizaciones en estado "En revisión" para aprobar o rechazar.
 */
class ReviewController extends Controller
{
    /**
     * Muestra el panel con cotizaciones pendientes de revisión.
     * Incluye información del remitente y su rol.
     */
    public function index(Request $request): View
    {
        $underReview = Quotation::where('status', 'under_review')
            ->with(['prospect', 'createdBy', 'items'])
            ->orderByDesc('created_at')
            ->paginate(config('kontbox.items_per_page'));

        AuditService::logBusiness('Accedió al panel de revisión');

        return view('reviews.index', compact('underReview'));
    }
}
