<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ContraparteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\RecorrenciaController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

        
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('empresas/create', [EmpresaController::class, 'create'])->name('empresas.create');
    Route::post('empresas', [EmpresaController::class, 'store'])->name('empresas.store');
    Route::get('empresas/selecionar', [EmpresaController::class, 'selecionar'])->name('empresas.selecionar');
    Route::post('empresas/selecionar', [EmpresaController::class, 'armazenarSelecao'])->name('empresas.selecionar.armazenar');
    Route::match(['put', 'patch'], 'empresas/{empresa}', [EmpresaController::class, 'update'])->name('empresas.update');
    Route::delete('empresas/{empresa}', [EmpresaController::class, 'destroy'])->name('empresas.destroy');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'empresa.context',
    'empresa.obrigatoria',
])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('relatorios/pdf', [RelatorioController::class, 'pdf'])->name('relatorios.pdf');
    Route::get('relatorios/excel', [RelatorioController::class, 'excel'])->name('relatorios.excel');

    Route::get('receitas', [ReceitaController::class, 'index'])->name('receitas.index');
    Route::post('receitas', [ReceitaController::class, 'store'])->name('receitas.store');
    Route::post('receitas/{lancamento}/status', [ReceitaController::class, 'status'])->name('receitas.status');
    Route::match(['put', 'patch'], 'receitas/{lancamento}', [ReceitaController::class, 'update'])->name('receitas.update');
    Route::delete('receitas/{lancamento}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');

    Route::get('despesas', [DespesaController::class, 'index'])->name('despesas.index');
    Route::post('despesas', [DespesaController::class, 'store'])->name('despesas.store');
    Route::post('despesas/{lancamento}/status', [DespesaController::class, 'status'])->name('despesas.status');
    Route::match(['put', 'patch'], 'despesas/{lancamento}', [DespesaController::class, 'update'])->name('despesas.update');
    Route::delete('despesas/{lancamento}', [DespesaController::class, 'destroy'])->name('despesas.destroy');

    Route::get('recorrencias', [RecorrenciaController::class, 'index'])->name('recorrencias.index');
    Route::post('recorrencias', [RecorrenciaController::class, 'store'])->name('recorrencias.store');
    Route::post('recorrencias/{recorrencia}/regenerar', [RecorrenciaController::class, 'regenerar'])->name('recorrencias.regenerar');
    Route::match(['put', 'patch'], 'recorrencias/{recorrencia}', [RecorrenciaController::class, 'update'])->name('recorrencias.update');
    Route::delete('recorrencias/{recorrencia}', [RecorrenciaController::class, 'destroy'])->name('recorrencias.destroy');

    Route::get('contrapartes', [ContraparteController::class, 'index'])->name('contrapartes.index');
    Route::post('contrapartes', [ContraparteController::class, 'store'])->name('contrapartes.store');
    Route::match(['put', 'patch'], 'contrapartes/{contraparte}', [ContraparteController::class, 'update'])->name('contrapartes.update');
    Route::delete('contrapartes/{contraparte}', [ContraparteController::class, 'destroy'])->name('contrapartes.destroy');

    Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::match(['put', 'patch'], 'categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
});
