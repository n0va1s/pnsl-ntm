<?php

use App\Http\Controllers\{
    ConfiguracoesController,
    EventoController,
    FichaVemController,
    FichaEccController,
    TipoMovimentoController,
    TipoResponsavelController,
    TipoSituacaoController,
    TrabalhadorController,
};


use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get(
    '/configuracoes',
Route::get('/configuracoes',
    [ConfiguracoesController::class, 'index']
)->name('configuracoes.index');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
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
