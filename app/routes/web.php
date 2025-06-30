<?php

use App\Http\Controllers\{
    ConfiguracoesController,
    EventoController,
    FichaVemController,
    FichaEccController,
    HomeController,
    TipoMovimentoController,
    TipoResponsavelController,
    TipoSituacaoController,
    TrabalhadorController,
};


use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::post(
    '/',
    [HomeController::class, 'contato']
)->name('home.contato');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get(
        '/configuracoes',
        [ConfiguracoesController::class, 'index']
    )->name('configuracoes.index');

    Route::get('fichas-vem/approve/{id}', [FichaVemController::class, 'approve'])
        ->name('fichas-vem.approve');
    Route::get('fichas-ecc/approve/{id}', [FichaEccController::class, 'approve'])
        ->name('fichas-ecc.approve');
    Route::resources([
        'eventos' => EventoController::class,
        'fichas-vem' => FichaVemController::class,
        'fichas-ecc' => FichaEccController::class,
        'tiposmovimentos' => TipoMovimentoController::class,
        'tiporesponsavel' => TipoResponsavelController::class,
        'tiposituacao' => TipoSituacaoController::class,
    ]);

    Route::resource('trabalhadores', TrabalhadorController::class)
        ->parameters([
            'trabalhadores' => 'idt_pessoa',
        ]);

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
