<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OperationFormOptionController;
use App\Http\Controllers\OperationStatusController;
use App\Http\Controllers\PaperSizeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PaperTypeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('paper-sizes', PaperSizeController::class);
    Route::post('operations/form-options', [OperationFormOptionController::class, 'store'])->name('operations.form-options.store');
    Route::patch('operations/{operation}/status', [OperationController::class, 'updateStatus'])->name('operations.update-status');
    Route::get('operations/{operation}/export', [OperationController::class, 'export'])->name('operations.export');
    Route::resource('operations', OperationController::class);
    Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::get('clients/template', [ClientController::class, 'template'])->name('clients.template');
    Route::post('clients/import', [ClientController::class, 'import'])->name('clients.import');
    Route::resource('clients', ClientController::class);

    Route::get('items/export', [ItemController::class, 'export'])->name('items.export');
    Route::get('items/template', [ItemController::class, 'template'])->name('items.template');
    Route::post('items/import', [ItemController::class, 'import'])->name('items.import');
    Route::resource('items', ItemController::class);

    Route::get('materials/export', [MaterialController::class, 'export'])->name('materials.export');
    Route::get('materials/template', [MaterialController::class, 'template'])->name('materials.template');
    Route::post('materials/import', [MaterialController::class, 'import'])->name('materials.import');
    Route::resource('materials', MaterialController::class);
    Route::resource('paper-types', PaperTypeController::class);
    Route::get('services/export', [ServiceController::class, 'export'])->name('services.export');
    Route::get('services/template', [ServiceController::class, 'template'])->name('services.template');
    Route::post('services/import', [ServiceController::class, 'import'])->name('services.import');
    Route::resource('services', ServiceController::class);
    Route::resource('stages', StageController::class);
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('reports/paper-materials-summary', [ReportController::class, 'paperMaterialsSummary'])->name('reports.paper-materials-summary');
    Route::get('reports/operations-kanban', [ReportController::class, 'operationsKanban'])->name('reports.operations-kanban');
    Route::get('reports/operations-kanban/load', [ReportController::class, 'operationsKanbanLoad'])->name('reports.operations-kanban.load');
    Route::resource('operation-statuses', OperationStatusController::class)->except(['show']);

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});
