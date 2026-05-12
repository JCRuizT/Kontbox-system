<?php

use App\Src\Infrastructure\Http\Controllers\Web\ActivityController;
use App\Src\Infrastructure\Http\Controllers\Web\AmendmentController;
use App\Src\Infrastructure\Http\Controllers\Web\ContractController;
use App\Src\Infrastructure\Http\Controllers\Web\InvoiceController;
use App\Src\Infrastructure\Http\Controllers\Web\MicroserviceController;
use App\Src\Infrastructure\Http\Controllers\Web\PlanController;
use App\Src\Infrastructure\Http\Controllers\Web\ProspectController;
use App\Src\Infrastructure\Http\Controllers\Web\QuotationController;
use App\Src\Infrastructure\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('login', fn () => view('auth.login'))->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => view('dashboard.index'))->name('dashboard');

    // Módulo: Configuración del sistema (Microservicios, Planes, Actividades)

    Route::middleware(['permission:microservices.read'])->group(function () {
        Route::resource('microservices', MicroserviceController::class)->except(['show', 'destroy']);
        Route::delete('microservices/{microservice}', [MicroserviceController::class, 'destroy'])
            ->name('microservices.destroy')
            ->middleware('permission:microservices.deactivate');
        Route::post('microservices/{microservice}/activate', [MicroserviceController::class, 'activate'])
            ->name('microservices.activate')
            ->middleware('permission:microservices.deactivate');
    });

    Route::middleware(['permission:plans.read'])->group(function () {
        Route::resource('plans', PlanController::class)->except(['show']);
        Route::post('plans/{plan}/activate', [PlanController::class, 'activate'])
            ->name('plans.activate')
            ->middleware('permission:plans.deactivate');
        Route::post('plans/{plan}/activities/{activity}/toggle', [PlanController::class, 'toggleActivity'])
            ->name('plans.activities.toggle')
            ->middleware('permission:plans.update');
    });

    Route::middleware(['permission:activities.read'])->group(function () {
        Route::resource('activities', ActivityController::class)->except(['show', 'destroy']);
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])
            ->name('activities.destroy')
            ->middleware('permission:activities.deactivate');
        Route::post('activities/{activity}/activate', [ActivityController::class, 'activate'])
            ->name('activities.activate')
            ->middleware('permission:activities.deactivate');
    });

    // Módulo: Gestión comercial (Prospectos, Cotizaciones)
    Route::middleware(['permission:prospects.read'])->group(function () {
        Route::resource('prospects', ProspectController::class);
    });

    Route::middleware(['permission:quotations.read'])->group(function () {
        Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('quotations/{quotation}/send-for-approval', [QuotationController::class, 'sendForApproval'])
            ->name('quotations.send-for-approval')
            ->middleware('permission:quotations.send_for_approval');
        Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])
            ->name('quotations.approve')
            ->middleware('permission:quotations.approve');
        Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])
            ->name('quotations.reject')
            ->middleware('permission:quotations.reject');
        Route::post('quotations/{quotation}/new-version', [QuotationController::class, 'newVersion'])
            ->name('quotations.new-version')
            ->middleware('permission:quotations.create');
    });

    // Módulo: Aprobación (Panel de revisión)
    Route::get('reviews', [\App\Src\Infrastructure\Http\Controllers\Web\ReviewController::class, 'index'])
        ->name('reviews.index')
        ->middleware('permission:quotations.approve');

    // Módulo: Contractual (Contratos, Modificaciones)
    Route::middleware(['permission:contracts.read'])->group(function () {
        Route::resource('contracts', ContractController::class)->only(['index', 'store', 'show']);
        Route::get('contracts/create/{quotation}', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('contracts/{contract}/upload-document', [ContractController::class, 'uploadDocument'])
            ->name('contracts.upload-document')
            ->middleware('permission:contracts.upload_document');
        Route::post('contracts/{contract}/activate', [ContractController::class, 'activate'])
            ->name('contracts.activate')
            ->middleware('permission:contracts.activate');
        Route::post('contracts/{contract}/anulate', [ContractController::class, 'anulate'])
            ->name('contracts.anulate')
            ->middleware('permission:contracts.anulate');

    });

    Route::middleware(['permission:amendments.create'])->group(function () {
        Route::get('contracts/{contract}/amendments/create', [AmendmentController::class, 'create'])->name('amendments.create');
        Route::post('amendments', [AmendmentController::class, 'store'])->name('amendments.store');
    });

    Route::middleware(['permission:amendments.read'])->group(function () {
        Route::get('amendments', [AmendmentController::class, 'index'])->name('amendments.index');
        Route::get('amendments/{amendment}', [AmendmentController::class, 'show'])->name('amendments.show');
    });

    // Búsqueda dinámica (selectores AJAX)
    Route::get('search/prospects', [SearchController::class, 'prospects'])->name('search.prospects')->middleware('permission:prospects.read');
    Route::get('search/plans', [SearchController::class, 'plans'])->name('search.plans')->middleware('permission:plans.read');
    Route::get('search/microservices', [SearchController::class, 'microservices'])->name('search.microservices')->middleware('permission:microservices.read');
    Route::get('search/contracts', [SearchController::class, 'contracts'])->name('search.contracts')->middleware('permission:contracts.read');
    Route::get('search/users', [SearchController::class, 'users'])->name('search.users')->middleware('permission:admin.access');

    // Auditoría y trazabilidad
    Route::view('audit', 'audit.index')
        ->name('audit.index')
        ->middleware('permission:audit.read');

    // PDF: visualización de documentos firmados
    Route::get('pdf/contract/{contract}', [\App\Src\Infrastructure\Http\Controllers\Web\PdfController::class, 'contract'])
        ->name('pdf.contract')->middleware('permission:contracts.read');
    Route::get('pdf/amendment/{amendment}', [\App\Src\Infrastructure\Http\Controllers\Web\PdfController::class, 'amendment'])
        ->name('pdf.amendment')->middleware('permission:amendments.read');

    // Módulo: Financiero (Facturación)
    Route::middleware(['permission:invoices.read'])->group(function () {
        Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    });

    // Módulo: Administración (Usuarios, Roles) — protegido por permiso dinámico admin.access
    Route::middleware(['permission:admin.access'])->prefix('admin')->name('admin.')->group(function () {
        // Usuarios
        Route::get('users', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'users'])->name('users');
        Route::get('users/create', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersCreate'])->name('users.create');
        Route::post('users', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersStore'])->name('users.store');
        Route::get('users/{user}/edit', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersEdit'])->name('users.edit');
        Route::put('users/{user}', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersUpdate'])->name('users.update');
        Route::post('users/{user}/delete', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersDelete'])->name('users.delete');
        Route::post('users/{id}/restore', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'usersRestore'])->name('users.restore');
        // Roles
        Route::get('roles', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'roles'])->name('roles');
        Route::get('roles/create', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'rolesCreate'])->name('roles.create');
        Route::post('roles', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'rolesStore'])->name('roles.store');
        Route::get('roles/{role}/edit', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'rolesEdit'])->name('roles.edit');
        Route::put('roles/{role}', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'rolesUpdate'])->name('roles.update');
        Route::put('roles/{role}/rename', [\App\Src\Infrastructure\Http\Controllers\Web\AdminController::class, 'rolesRename'])->name('roles.rename');
    });
});
