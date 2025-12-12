<?php

use App\Http\Controllers\AniversarioController;
use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\FichaEccController;
use App\Http\Controllers\FichaSGMController;
use App\Http\Controllers\FichaVemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipanteController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoEquipeController;
use App\Http\Controllers\TipoMovimentoController;
use App\Http\Controllers\TipoPerfilController;
use App\Http\Controllers\TipoResponsavelController;
use App\Http\Controllers\TipoRestricaoController;
use App\Http\Controllers\TipoSituacaoController;
use App\Http\Controllers\TrabalhadorController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Route::get(
        '/',
        [HomeController::class, 'index']
    )->name('home');

    Route::post(
        '/',
        [HomeController::class, 'contato']
    )->name('home.contato');

    Route::get('/vem', [HomeController::class, 'fichaVem'])
        ->name('home.ficha.vem');
    Route::get('/ecc', [HomeController::class, 'fichaEcc'])
        ->name('home.ficha.ecc');
    Route::get('/sgm', [HomeController::class, 'fichaSgm'])
        ->name('home.ficha.sgm');
});

// Area Administrativa
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get(
        '/timeline',
        [EventoController::class, 'timeline']
    )->name('timeline.index');

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

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

    Route::get('fichas/vem/approve/{id}', [FichaVemController::class, 'approve'])
        ->name('vem.approve');
    Route::get('fichas/ecc/approve/{id}', [FichaEccController::class, 'approve'])
        ->name('ecc.approve');
    Route::get('fichas/sgm/approve/{id}', [FichaSGMController::class, 'approve'])
        ->name('sgm.approve');

    Route::resources([
        'eventos' => EventoController::class,
        'pessoas' => PessoaController::class,
        'fichas/vem' => FichaVemController::class,
        'fichas/ecc' => FichaEccController::class,
        'fichas/sgm' => FichaSGMController::class,
    ]);

    Route::get('/aniversario', [AniversarioController::class, 'index'])->name('aniversario.index');

    // Somente admin
    Route::middleware(['admin'])->group(function () {
        Route::get(
            '/configuracoes',
            [ConfiguracoesController::class, 'index']
        )->name('configuracoes.index')->middleware('admin');

        Route::get('/configuracoes/role', [TipoPerfilController::class, 'index'])->name('role.index')->middleware('admin');
        Route::post('/configuracoes/role', [TipoPerfilController::class, 'store'])->name('role.store')->middleware('admin');
        Route::post('/configuracoes/role/change', [TipoPerfilController::class, 'change'])->name('role.change')->middleware('admin');

        Route::resources([
            'configuracoes/equipe' => TipoEquipeController::class,
            'configuracoes/movimento' => TipoMovimentoController::class,
            'configuracoes/responsavel' => TipoResponsavelController::class,
            'configuracoes/restricao' => TipoRestricaoController::class,
            'configuracoes/situacao' => TipoSituacaoController::class,
        ]);
    });

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
