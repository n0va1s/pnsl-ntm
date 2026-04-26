<?php

use Illuminate\Support\Facades\Route;
use Modules\Vendinha\Http\Controllers\ProdutoController;
use Modules\Vendinha\Http\Controllers\VendaController;
use Modules\Vendinha\Http\Controllers\VendinhaDashboardController;

Route::get('/', VendinhaDashboardController::class)->name('dashboard');

Route::get('/produtos/novo', [ProdutoController::class, 'create'])->name('produtos.create');
Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
Route::get('/produtos/{produto}/editar', [ProdutoController::class, 'edit'])->name('produtos.edit');
Route::put('/produtos/{produto}', [ProdutoController::class, 'update'])->name('produtos.update');

Route::get('/vendas/nova', [VendaController::class, 'create'])->name('vendas.create');
Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');
Route::post('/vendas/{venda}/pagar', [VendaController::class, 'pagar'])->name('vendas.pagar');
