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
    Route::middleware('permission:quotations.read')->group(function () {
        Route::get('quotations', [QuotationApiController::class, 'index'])->name('quotations.index');
        Route::get('quotations/{quotation}', [QuotationApiController::class, 'show'])->name('quotations.show');
    });
    Route::post('quotations', [QuotationApiController::class, 'store'])->name('quotations.store')->middleware('permission:quotations.create');
    Route::post('quotations/{quotation}/send-for-approval', [QuotationApiController::class, 'sendForApproval'])->middleware('permission:quotations.send_for_approval');
    Route::post('quotations/{quotation}/approve', [QuotationApiController::class, 'approve'])->middleware('permission:quotations.approve');
    Route::post('quotations/{quotation}/reject', [QuotationApiController::class, 'reject'])->middleware('permission:quotations.reject');

    // Contracts CRUD + document upload, activation, cancellation
    Route::middleware('permission:contracts.read')->group(function () {
        Route::get('contracts', [ContractApiController::class, 'index'])->name('contracts.index');
        Route::get('contracts/{contract}', [ContractApiController::class, 'show'])->name('contracts.show');
    });
    Route::post('contracts', [ContractApiController::class, 'store'])->name('contracts.store')->middleware('permission:contracts.create');
    Route::post('contracts/{contract}/upload-document', [ContractApiController::class, 'uploadDocument'])->middleware('permission:contracts.upload_document');
    Route::post('contracts/{contract}/activate', [ContractApiController::class, 'activate'])->middleware('permission:contracts.activate');
    Route::post('contracts/{contract}/anulate', [ContractApiController::class, 'anulate'])->middleware('permission:contracts.anulate');

    // Amendments CRUD
    Route::middleware('permission:amendments.read')->group(function () {
        Route::get('amendments', [AmendmentApiController::class, 'index'])->name('amendments.index');
        Route::get('amendments/{amendment}', [AmendmentApiController::class, 'show'])->name('amendments.show');
    });
    Route::post('amendments', [AmendmentApiController::class, 'store'])->name('amendments.store')->middleware('permission:amendments.create');
});
