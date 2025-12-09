<?php

use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\TipoEquipe;
use App\Models\Trabalhador;
use App\Models\Voluntario;
use App\Services\VoluntarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VoluntarioService();
    $this->movimento = \App\Models\TipoMovimento::factory()->create();
    $this->pessoa = Pessoa::factory()->create();
    $this->evento = Evento::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $this->equipe1 = TipoEquipe::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
    $this->equipe2 = TipoEquipe::factory()->create(['idt_movimento' => $this->movimento->idt_movimento]);
});

describe('VoluntarioService', function () {
    test('pode realizar candidatura com sucesso', function () {
        $equipesData = [
            $this->equipe1->idt_equipe => [
                'selecionado' => '1',
                'habilidade' => 'Habilidade válida',
            ],
        ];

        $this->service->candidatura($equipesData, $this->evento->idt_evento, $this->pessoa);

        $this->assertDatabaseHas('voluntario', [
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $this->evento->idt_evento,
            'idt_equipe' => $this->equipe1->idt_equipe,
            'txt_habilidade' => 'Habilidade válida',
        ]);
    });

    test('falha candidatura com habilidade curta', function () {
        $equipesData = [
            $this->equipe1->idt_equipe => [
                'selecionado' => '1',
                'habilidade' => 'Curto',
            ],
        ];

        expect(fn () => $this->service->candidatura($equipesData, $this->evento->idt_evento, $this->pessoa))
            ->toThrow(ValidationException::class);
    });

    test('falha candidatura com caracteres repetidos', function () {
        $equipesData = [
            $this->equipe1->idt_equipe => [
                'selecionado' => '1',
                'habilidade' => 'Aaaaaa',
            ],
        ];

        expect(fn () => $this->service->candidatura($equipesData, $this->evento->idt_evento, $this->pessoa))
            ->toThrow(ValidationException::class);
    });

    test('falha candidatura sem nenhuma equipe', function () {
        $equipesData = [];

        expect(fn () => $this->service->candidatura($equipesData, $this->evento->idt_evento, $this->pessoa))
            ->toThrow(ValidationException::class);
    });

    test('pode confirmar voluntario', function () {
        $voluntario = Voluntario::factory()->create([
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $this->evento->idt_evento,
            'idt_equipe' => $this->equipe1->idt_equipe,
        ]);

        $this->service->confirmacao(
            $voluntario->idt_voluntario,
            $this->equipe1->idt_equipe,
            true, // coordenador
            true  // primeira vez
        );

        $this->assertDatabaseHas('trabalhador', [
            'idt_pessoa' => $this->pessoa->idt_pessoa,
            'idt_evento' => $this->evento->idt_evento,
            'idt_equipe' => $this->equipe1->idt_equipe,
            'ind_coordenador' => true,
            'ind_primeira_vez' => true,
        ]);

        $voluntario->refresh();
        expect($voluntario->idt_trabalhador)->not->toBeNull();
    });
});
