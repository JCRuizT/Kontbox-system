<?php

use App\Src\Infrastructure\Http\Controllers\Api\AmendmentApiController;
use App\Src\Infrastructure\Http\Controllers\Api\ContractApiController;
use App\Src\Infrastructure\Http\Controllers\Api\MicroserviceApiController;
use App\Src\Infrastructure\Http\Controllers\Api\QuotationApiController;
use Illuminate\Support\Facades\Route;

// All API routes require Sanctum authentication
Route::middleware('auth:sanctum')->name('api.')->group(function () {

    // Microservices CRUD
    Route::apiResource('microservices', MicroserviceApiController::class);

    // Quotations flow: CRUD + approval workflow
    Route::apiResource('quotations', QuotationApiController::class)->only(['index', 'store', 'show']);
    Route::post('quotations/{quotation}/send-for-approval', [QuotationApiController::class, 'sendForApproval']);
    Route::post('quotations/{quotation}/approve', [QuotationApiController::class, 'approve']);
    Route::post('quotations/{quotation}/reject', [QuotationApiController::class, 'reject']);

    // Contracts CRUD + document upload, activation, cancellation
    Route::apiResource('contracts', ContractApiController::class)->only(['index', 'store', 'show']);
    Route::post('contracts/{contract}/upload-document', [ContractApiController::class, 'uploadDocument']);
    Route::post('contracts/{contract}/activate', [ContractApiController::class, 'activate']);
    Route::post('contracts/{contract}/anulate', [ContractApiController::class, 'anulate']);

    // Amendments CRUD
    Route::apiResource('amendments', AmendmentApiController::class)->only(['index', 'store', 'show']);
});
