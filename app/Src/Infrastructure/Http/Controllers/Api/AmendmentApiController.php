<?php

namespace App\Src\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API para la gestión de anexos de contratos.
 */
class AmendmentApiController extends Controller
{
    /**
     * Retorna todos los anexos con relaciones.
     */
    public function index(): JsonResponse
    {
        $amendments = ContractAmendment::with(['contract.quotation.prospect', 'createdBy'])
            ->orderByDesc('created_at')
            ->get();
        return response()->json($amendments);
    }

    /**
     * Crea un anexo con PDF firmado obligatorio.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'description' => 'required|string|min:10',
            'signed_pdf' => 'required|file|mimes:pdf|max:10240',
            'modified_services' => 'nullable|json',
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);

        if ($contract->status !== ContractStatus::ACTIVE->value) {
            return response()->json(['error' => __('domain.amendment.only_active_contracts')], 422);
        }

        $pdfPath = $validated['signed_pdf']->store('amendments/' . $contract->id);

        $amendment = ContractAmendment::create([
            'contract_id' => $contract->id,
            'amendment_number' => 'OTR-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'description' => $validated['description'],
            'signed_pdf_path' => $pdfPath,
            'modified_services' => $validated['modified_services'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($amendment, 201);
    }

    /**
     * Retorna un anexo específico con sus relaciones.
     */
    public function show(ContractAmendment $amendment): JsonResponse
    {
        $amendment->load(['contract.quotation.prospect', 'createdBy']);
        return response()->json($amendment);
    }
}
