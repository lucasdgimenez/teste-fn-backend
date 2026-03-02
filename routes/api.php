<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\VendasController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/',        [ProdutosController::class, 'index'])  ->name('index');
        Route::post('/',       [ProdutosController::class, 'store'])  ->name('store');
        Route::get('/{id}',    [ProdutosController::class, 'show'])   ->name('show');
        Route::put('/{id}',    [ProdutosController::class, 'update']) ->name('update');
        Route::delete('/{id}', [ProdutosController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('admin')->prefix('compras')->name('compras.')->group(function () {
        Route::get('/',        [ComprasController::class, 'index'])  ->name('index');
        Route::post('/',       [ComprasController::class, 'store'])  ->name('store');
        Route::get('/{id}',    [ComprasController::class, 'show'])   ->name('show');
        Route::put('/{id}',    [ComprasController::class, 'update']) ->name('update');
        Route::delete('/{id}', [ComprasController::class, 'destroy'])->name('destroy');
    }); 

    Route::prefix('vendas')->name('vendas.')->group(function () {
        Route::get('/',              [VendasController::class, 'index'])   ->name('index');
        Route::post('/',             [VendasController::class, 'store'])   ->name('store');
        Route::get('/{id}',          [VendasController::class, 'show'])    ->name('show');
        Route::put('/{id}',          [VendasController::class, 'update'])  ->name('update');
        Route::patch('/{id}/cancelar', [VendasController::class, 'cancelar'])->name('cancelar');
        Route::delete('/{id}',       [VendasController::class, 'destroy']) ->name('destroy');
    });

});
