<?php

namespace App\Src\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Src\Application\UseCases\Quotations\ApproveQuotationUseCase;
use App\Src\Application\UseCases\Quotations\RejectQuotationUseCase;
use App\Src\Application\UseCases\Quotations\SendQuotationForApprovalUseCase;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API para la gestión de cotizaciones con ciclo de aprobación.
 */
class QuotationApiController extends Controller
{
    /**
     * Retorna todas las cotizaciones con relaciones.
     */
    public function index(): JsonResponse
    {
        $quotations = Quotation::with(['prospect', 'createdBy', 'items'])
            ->orderByDesc('created_at')
            ->get();
        return response()->json($quotations);
    }

    /**
     * Crea una cotización en estado Borrador.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prospect_id' => 'required|exists:prospects,id',
            'plan_id' => 'nullable|exists:plans,id',
            'valid_until' => 'nullable|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.microservice_id' => 'required|exists:microservices,id',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $pricing = app(\App\Src\Application\Services\QuotationPricingService::class)->calculate($validated['items']);
        $subtotal = $pricing['subtotal'];
        $tax = $pricing['tax'];
        $total = $subtotal + $tax;

        $quotation = Quotation::create([
            'quote_number' => 'COT-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'prospect_id' => $validated['prospect_id'],
            'plan_id' => $validated['plan_id'] ?? null,
            'created_by' => $request->user()->id,
            'status' => QuotationStatus::DRAFT->value,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'valid_until' => $validated['valid_until'] ?? null,
            'version' => 1,
        ]);

        foreach ($validated['items'] as $item) {
            $ms = Microservice::find($item['microservice_id']);
            $quotation->items()->create([
                'microservice_id' => $item['microservice_id'],
                'service_name_snapshot' => $ms->name,
                'description_snapshot' => $ms->description,
                'unit_price' => $item['unit_price'],
                'total_price' => $item['unit_price'],
            ]);
        }

        $quotation->load('items');
        AuditService::logCreate($quotation, 'Cotización (API)', $validated);
        return response()->json($quotation, 201);
    }

    /**
     * Retorna una cotización con sus items y relaciones.
     */
    public function show(Quotation $quotation): JsonResponse
    {
        $quotation->load(['prospect', 'createdBy', 'items.microservice']);
        return response()->json($quotation);
    }

    /**
     * Envía una cotización a aprobación (cambia a En revisión).
     */
    public function sendForApproval(int $id): JsonResponse
    {
        try {
            app(SendQuotationForApprovalUseCase::class)->execute($id);
            return response()->json(['message' => __('domain.quotation.sent_for_approval')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        try {
            app(ApproveQuotationUseCase::class)->execute($id);
            return response()->json(['message' => __('domain.quotation.approved')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['reason' => 'required|string|min:10']);
        try {
            app(RejectQuotationUseCase::class)->execute($id, $validated['reason']);
            return response()->json(['message' => __('domain.quotation.rejected')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }
}
