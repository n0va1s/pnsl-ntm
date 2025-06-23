<?php

use App\Http\Controllers\{ConfiguracoesController, EventoController, FichaController, TipoMovimentoController};
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
    ]);

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
