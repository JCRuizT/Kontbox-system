<?php

namespace App\Src\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Src\Application\UseCases\Contracts\ActivateContractUseCase;
use App\Src\Application\UseCases\Contracts\AnulateContractUseCase;
use App\Src\Application\UseCases\Contracts\UploadDocumentUseCase;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API para la gestión de contratos con flujo de documentos.
 */
class ContractApiController extends Controller
{
    /**
     * Retorna todos los contratos con relaciones.
     */
    public function index(): JsonResponse
    {
        $contracts = Contract::with(['quotation.prospect', 'approvedBy', 'services'])
            ->orderByDesc('created_at')
            ->get();
        return response()->json($contracts);
    }

    /**
     * Crea un contrato a partir de una cotización aprobada.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);

        if ($quotation->status !== 'approved') {
            return response()->json(['error' => __('domain.contract.quotation_not_approved')], 422);
        }

        if (Contract::where('quotation_id', $quotation->id)->exists()) {
            return response()->json(['error' => __('domain.contract.quotation_already_contracted')], 409);
        }

        $contract = Contract::create([
            'contract_number' => 'CTR-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'quotation_id' => $quotation->id,
            'approved_by' => $request->user()->id,
            'status' => 'pending_document',
            'total_amount' => $validated['total_amount'],
        ]);

        foreach ($quotation->items as $item) {
            $contract->services()->create([
                'microservice_id' => $item->microservice_id,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        $contract->load('services');
        AuditService::logCreate($contract, 'Contrato (API)', $validated);
        return response()->json($contract, 201);
    }

    /**
     * Retorna un contrato con todas sus relaciones.
     */
    public function show(Contract $contract): JsonResponse
    {
        $contract->load(['quotation.prospect', 'approvedBy', 'services.microservice', 'amendments']);
        return response()->json($contract);
    }

    /**
     * Carga el PDF firmado del contrato.
     */
    public function uploadDocument(Request $request, Contract $contract): JsonResponse
    {
        $validated = $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $validated['signed_pdf']->store('contracts/' . $contract->id);

        $useCase = app(UploadDocumentUseCase::class);
        try {
            $useCase->execute(
                $contract->id,
                $path,
                $validated['signed_pdf']->getClientOriginalName(),
                $validated['signed_pdf']->getSize()
            );

            return response()->json(['message' => __('domain.contract.document_uploaded')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }

    /**
     * Activa un contrato (requiere PDF cargado previamente).
     */
    public function activate(int $id): JsonResponse
    {
        try {
            app(ActivateContractUseCase::class)->execute($id);
            return response()->json(['message' => __('domain.contract.activated_successfully')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }

    public function anulate(Request $request, Contract $contract): JsonResponse
    {
        $validated = $request->validate(['reason' => 'required|string|min:10']);
        try {
            app(AnulateContractUseCase::class)->execute($contract->id, $validated['reason']);
            return response()->json(['message' => __('domain.contract.anulated')]);
        } catch (\DomainException $e) {
            return response()->json(['error' => __($e->getMessage())], 422);
        }
    }
}
