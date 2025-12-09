<?php

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Voluntario;
use App\Services\VoluntarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VoluntarioService();
});

test('candidatura throws validation exception if skill is empty', function () {
    $pessoa = Pessoa::factory()->create();
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create();

    $equipesData = [
        $equipe->idt_equipe => [
            'selecionado' => '1',
            'habilidade' => ''
        ]
    ];

    expect(fn () => $this->service->candidatura($equipesData, $evento->idt_evento, $pessoa))
        ->toThrow(ValidationException::class);
});

test('candidatura throws validation exception if skill is too short', function () {
    $pessoa = Pessoa::factory()->create();
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create();

    $equipesData = [
        $equipe->idt_equipe => [
            'selecionado' => '1',
            'habilidade' => 'curto' // 5 chars, needs > 5
        ]
    ];

    expect(fn () => $this->service->candidatura($equipesData, $evento->idt_evento, $pessoa))
        ->toThrow(ValidationException::class);
});

test('candidatura throws validation exception if skill has repeated chars', function () {
    $pessoa = Pessoa::factory()->create();
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create();

    $equipesData = [
        $equipe->idt_equipe => [
            'selecionado' => '1',
            'habilidade' => 'Eu gosto de aaaaa' // repeated chars
        ]
    ];

    expect(fn () => $this->service->candidatura($equipesData, $evento->idt_evento, $pessoa))
        ->toThrow(ValidationException::class);
});

test('candidatura creates voluntario records successfully', function () {
    $pessoa = Pessoa::factory()->create();
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create();

    $equipesData = [
        $equipe->idt_equipe => [
            'selecionado' => '1',
            'habilidade' => 'Habilidade válida para teste'
        ]
    ];

    $this->service->candidatura($equipesData, $evento->idt_evento, $pessoa);

    $this->assertDatabaseHas('voluntario', [
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe,
        'txt_habilidade' => 'Habilidade válida para teste'
    ]);
});

test('confirmacao promotes voluntario to trabalhador', function () {
    $pessoa = Pessoa::factory()->create();
    $evento = Evento::factory()->create();
    $equipe = TipoEquipe::factory()->create();

    $voluntario = Voluntario::factory()->create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe
    ]);

    $this->service->confirmacao(
        $voluntario->idt_voluntario,
        $equipe->idt_equipe,
        true, // isCoordenador
        true  // isPrimeiraVez
    );

    $this->assertDatabaseHas('trabalhador', [
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_evento' => $evento->idt_evento,
        'idt_equipe' => $equipe->idt_equipe,
        'ind_coordenador' => true,
        'ind_primeira_vez' => true
    ]);

    $voluntario->refresh();
    expect($voluntario->idt_trabalhador)->not->toBeNull();
});
