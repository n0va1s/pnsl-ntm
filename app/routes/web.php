<?php

use App\Http\Controllers\{
    ConfiguracoesController,
    ContatoController,
    DashboardController,
    EventoController,
    FichaVemController,
    FichaEccController,
    HomeController,
    ParticipanteController,
    PessoaController,
    TipoMovimentoController,
    TipoResponsavelController,
    TipoSituacaoController,
    TrabalhadorController,
    AniversarioController,
    PerfilUsuarioController,
};


use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Area Publica
Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::post(
    '/',
    [HomeController::class, 'contato']
)->name('home.contato');



// Area Administrativa
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/configuracoes',
        [ConfiguracoesController::class, 'index']
    )->name('configuracoes.index');
    Route::get(
        '/contatos',
        [ContatoController::class, 'index']
    )->name('contatos.index');
    Route::delete(
        '/contatos/{id}',
        [ContatoController::class, 'destroy']
    )->name('contatos.destroy');

    Route::get(
        '/participantes',
        [ParticipanteController::class, 'index']
    )->name('participantes.index');

    Route::post(
        '/participantes',
        [ParticipanteController::class, 'change']
    )->name('participantes.change');

    Route::post(
        '/participantes/{evento}/{pessoa}',
        [EventoController::class, 'confirm']
    )->name('participantes.confirm');

    Route::get(
        '/trabalhadores',
        [TrabalhadorController::class, 'index']
    )->name('trabalhadores.index');

    Route::get(
        '/trabalhadores/create',
        [TrabalhadorController::class, 'create']
    )->name('trabalhadores.create');

    Route::post(
        '/trabalhadores',
        [TrabalhadorController::class, 'store']
    )->name('trabalhadores.store');

    Route::get(
        '/trabalhadores/review',
        [TrabalhadorController::class, 'review']
    )->name('trabalhadores.review');

    Route::post(
        '/avaliacao',
        [TrabalhadorController::class, 'send']
    )->name('avaliacao.send');

    Route::get(
        '/montagem',
        [TrabalhadorController::class, 'mount']
    )->name('montagem.list');

    Route::post(
        '/montagem',
        [TrabalhadorController::class, 'confirm']
    )->name('montagem.confirm');

    Route::get(
        '/quadrante',
        [TrabalhadorController::class, 'generate']
    )->name('quadrante.list');

    Route::get('fichas-vem/approve/{id}', [FichaVemController::class, 'approve'])
        ->name('fichas-vem.approve');
    Route::get('fichas-ecc/approve/{id}', [FichaEccController::class, 'approve'])
        ->name('fichas-ecc.approve');


    Route::get('/aniversario', [AniversarioController::class, 'index'])->name('aniversario.index');

    Route::post('/perfilusuario', [PerfilUsuarioController::class, 'store'])->name('perfilusuario.store');

    Route::post('/configuracoes/perfilusuario/change', [PerfilUsuarioController::class, 'change'])->name('perfilusuario.change');


    Route::resources([
        'eventos' => EventoController::class,
        'fichas-vem' => FichaVemController::class,
        'fichas-ecc' => FichaEccController::class,
        'tiposmovimentos' => TipoMovimentoController::class,
        'tiporesponsavel' => TipoResponsavelController::class,
        'tiposituacao' => TipoSituacaoController::class,
        'pessoas' => PessoaController::class,
        'perfilusuario' => PerfilUsuarioController::class,
    ]);

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
