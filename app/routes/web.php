<?php

use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\TipoMovimentoController;
use App\Http\Controllers\TipoResponsavelController;
use App\Http\Controllers\TipoSituacaoController;
use App\Http\Controllers\TrabalhadorController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/configuracoes',
    [ConfiguracoesController::class, 'index']
)->name('configuracoes.index');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::resources([
        'eventos' => EventoController::class,
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

require __DIR__.'/auth.php';
