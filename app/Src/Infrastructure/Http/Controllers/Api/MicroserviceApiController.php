<?php

namespace App\Src\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API para la gestión de microservicios del catálogo.
 */
class MicroserviceApiController extends Controller
{
    /**
     * Retorna todos los microservicios activos.
     */
    public function index(): JsonResponse
    {
        $microservices = Microservice::where('is_active', true)->get();
        return response()->json($microservices);
    }

    /**
     * Crea un nuevo microservicio.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,one_time',
        ]);

        $microservice = Microservice::create($validated);
        AuditService::logCreate($microservice, 'Microservicio (API)', $validated);
        return response()->json($microservice, 201);
    }

    /**
     * Retorna un microservicio por ID.
     */
    public function show(Microservice $microservice): JsonResponse
    {
        return response()->json($microservice);
    }

    /**
     * Actualiza parcial o totalmente un microservicio.
     */
    public function update(Request $request, Microservice $microservice): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'sometimes|numeric|min:0',
            'type' => 'sometimes|in:recurring,one_time',
        ]);

        $original = $microservice->getOriginal();
        $microservice->update($validated);
        AuditService::logUpdate($microservice, 'Microservicio (API)', $original, $microservice->getChanges());
        return response()->json($microservice);
    }

    /**
     * Desactiva (baja lógica) un microservicio.
     */
    public function destroy(Microservice $microservice): JsonResponse
    {
        AuditService::logDelete($microservice, 'Microservicio (API)');
        $microservice->update(['is_active' => false]);
        return response()->json(['message' => __('domain.microservice.deactivated')]);
    }
}
