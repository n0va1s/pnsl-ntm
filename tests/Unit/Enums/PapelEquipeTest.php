<?php

use App\Enums\PapelEquipe;

describe('PapelEquipe enum', function () {
    test('tem exatamente 4 papeis', function () {
        expect(PapelEquipe::cases())->toHaveCount(4);
    });

    test('papeis estao na ordem correta', function () {
        $cases = PapelEquipe::cases();
        expect($cases[0])->toBe(PapelEquipe::CoordGeral);
        expect($cases[1])->toBe(PapelEquipe::CoordEquipeH);
        expect($cases[2])->toBe(PapelEquipe::CoordEquipeM);
        expect($cases[3])->toBe(PapelEquipe::MembroEquipe);
    });

    test('valores em snake_case corretos', function () {
        expect(PapelEquipe::CoordGeral->value)->toBe('coord_geral');
        expect(PapelEquipe::CoordEquipeH->value)->toBe('coord_equipe_h');
        expect(PapelEquipe::CoordEquipeM->value)->toBe('coord_equipe_m');
        expect(PapelEquipe::MembroEquipe->value)->toBe('membro_equipe');
    });

    test('label retorna strings pt_BR', function () {
        expect(PapelEquipe::CoordGeral->label())->toBe('Coordenador Geral');
        expect(PapelEquipe::CoordEquipeH->label())->toBe('Coordenador de Equipe H');
        expect(PapelEquipe::CoordEquipeM->label())->toBe('Coordenador de Equipe M');
        expect(PapelEquipe::MembroEquipe->label())->toBe('Membro de Equipe');
    });

    test('opcoes() devolve array value=>label com 4 pares', function () {
        $opcoes = PapelEquipe::opcoes();
        expect($opcoes)->toBeArray();
        expect($opcoes)->toHaveCount(4);
        expect($opcoes['coord_geral'])->toBe('Coordenador Geral');
        expect($opcoes['coord_equipe_h'])->toBe('Coordenador de Equipe H');
        expect($opcoes['coord_equipe_m'])->toBe('Coordenador de Equipe M');
        expect($opcoes['membro_equipe'])->toBe('Membro de Equipe');
    });

    test('isCoordenador distingue membro dos demais', function () {
        expect(PapelEquipe::CoordGeral->isCoordenador())->toBeTrue();
        expect(PapelEquipe::CoordEquipeH->isCoordenador())->toBeTrue();
        expect(PapelEquipe::CoordEquipeM->isCoordenador())->toBeTrue();
        expect(PapelEquipe::MembroEquipe->isCoordenador())->toBeFalse();
    });

    test('requerSexo retorna M/F/null conforme papel', function () {
        expect(PapelEquipe::CoordEquipeH->requerSexo())->toBe('M');
        expect(PapelEquipe::CoordEquipeM->requerSexo())->toBe('F');
        expect(PapelEquipe::CoordGeral->requerSexo())->toBeNull();
        expect(PapelEquipe::MembroEquipe->requerSexo())->toBeNull();
    });

    test('from() e tryFrom() funcionam corretamente', function () {
        expect(PapelEquipe::from('coord_geral'))->toBe(PapelEquipe::CoordGeral);
        expect(PapelEquipe::from('membro_equipe'))->toBe(PapelEquipe::MembroEquipe);
        expect(PapelEquipe::tryFrom('invalido'))->toBeNull();
        expect(PapelEquipe::tryFrom('coord_geral'))->toBe(PapelEquipe::CoordGeral);
    });
});
