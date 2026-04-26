<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\TipoMovimento;
use Database\Factories\EquipeFactory;

// Nota D-07: não reutiliza createMovimentos() para equipes VEM

describe('EquipeFactory', function () {
    beforeEach(function () {
        // Garantir que TipoMovimento VEM existe para as factories
        TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );
    });

    test('factory cria equipe valida', function () {
        $equipe = Equipe::factory()->create();

        expect($equipe)->toBeInstanceOf(Equipe::class);
        expect($equipe->idt_equipe)->toBeGreaterThan(0);
        expect($equipe->nom_equipe)->not->toBeEmpty();
        expect($equipe->des_slug)->not->toBeEmpty();
        expect($equipe->ind_ativa)->toBeTrue();
    });

    test('defaults() retorna 11 entradas com slugs corretos para acentos', function () {
        $factory = new EquipeFactory;
        $defaults = $factory->defaults();

        expect($defaults)->toHaveCount(11);

        // Verificar slugs de nomes com acentos pt_BR
        $slugs = array_column($defaults, 'des_slug');
        expect($slugs)->toContain('alimentacao');
        expect($slugs)->toContain('emaus');
        expect($slugs)->toContain('oracao');
        expect($slugs)->toContain('recepcao');
        expect($slugs)->toContain('troca-de-ideias');
        expect($slugs)->toContain('bandinha');
        expect($slugs)->toContain('limpeza');
        expect($slugs)->toContain('reportagem');
        expect($slugs)->toContain('sala');
        expect($slugs)->toContain('secretaria');
        expect($slugs)->toContain('vendinha');
    });

    test('seedDefaults e idempotente', function () {
        EquipeFactory::seedDefaults();
        $countAposUma = Equipe::count();

        EquipeFactory::seedDefaults();
        $countAposDuas = Equipe::count();

        expect($countAposUma)->toBe(11);
        expect($countAposDuas)->toBe(11); // não duplica
    });

    test('EquipeUsuarioFactory cria vinculo valido', function () {
        $ev = EquipeUsuario::factory()->create();

        expect($ev)->toBeInstanceOf(EquipeUsuario::class);
        expect($ev->idt_equipe_usuario)->toBeGreaterThan(0);
        expect($ev->papel)->toBe(PapelEquipe::MembroEquipe);
        expect($ev->idt_equipe)->toBeGreaterThan(0);
        expect($ev->user_id)->toBeGreaterThan(0);
    });

    test('EquipeUsuarioFactory comoPapel state funciona', function () {
        $ev = EquipeUsuario::factory()->comoPapel(PapelEquipe::CoordGeral)->create();
        expect($ev->papel)->toBe(PapelEquipe::CoordGeral);
    });
});
