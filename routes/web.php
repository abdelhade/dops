<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PaperSizeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OperationController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// CRUD Resources
Route::resource('categories', CategoryController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('paper-sizes', PaperSizeController::class);
Route::resource('items', ItemController::class);
Route::resource('operations', OperationController::class);
