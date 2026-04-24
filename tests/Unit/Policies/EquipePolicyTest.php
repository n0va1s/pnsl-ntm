<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use App\Policies\EquipePolicy;

describe('EquipePolicy', function () {

    beforeEach(function () {
        $this->policy = new EquipePolicy;
        $this->vem = TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );
        $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
    });

    // --- before() ---

    describe('before()', function () {

        it('retorna true para coord-geral independente da habilidade', function () {
            $coordGeral = User::factory()->create(['role' => 'user']); // role flat irrelevante
            $coordGeral->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordGeral->value,
            ]);

            // Testar via $user->can() — dispara Gate incluindo before()
            expect($coordGeral->can('viewAny', Equipe::class))->toBeTrue()
                ->and($coordGeral->can('view', $this->equipe))->toBeTrue()
                ->and($coordGeral->can('update', $this->equipe))->toBeTrue()
                ->and($coordGeral->can('assignMembers', $this->equipe))->toBeTrue();
        });

        it('retorna null para não-coord-geral, deixando o método específico decidir', function () {
            $membro = User::factory()->create();
            $membro->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::MembroEquipe->value,
            ]);

            // before() retorna null → viewAny() decide → true (qualquer autenticado)
            expect($membro->can('viewAny', Equipe::class))->toBeTrue();
            // before() retorna null → assignMembers() decide → false
            expect($membro->can('assignMembers', $this->equipe))->toBeFalse();
        });

    });

    // --- viewAny ---

    describe('viewAny()', function () {

        it('qualquer usuário autenticado pode listar equipes', function () {
            $user = User::factory()->create(['role' => 'user']);
            expect($this->policy->viewAny($user))->toBeTrue();
        });

        it('coord-geral pode listar via before() mesmo sem viewAny explícito', function () {
            $coordGeral = User::factory()->create(['role' => 'user']);
            $coordGeral->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordGeral->value,
            ]);
            expect($coordGeral->can('viewAny', Equipe::class))->toBeTrue();
        });

    });

    // --- view ---

    describe('view()', function () {

        it('membro-equipe pode ver a equipe da qual é membro', function () {
            $membro = User::factory()->create();
            $membro->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::MembroEquipe->value,
            ]);
            expect($this->policy->view($membro, $this->equipe))->toBeTrue();
        });

        it('coord-equipe-h pode ver a equipe', function () {
            $coordH = User::factory()->create();
            $coordH->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordEquipeH->value,
            ]);
            expect($this->policy->view($coordH, $this->equipe))->toBeTrue();
        });

        it('coord-equipe-m pode ver a equipe', function () {
            $coordM = User::factory()->create();
            $coordM->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordEquipeM->value,
            ]);
            expect($this->policy->view($coordM, $this->equipe))->toBeTrue();
        });

        it('usuário sem vínculo não pode ver a equipe', function () {
            $semVinculo = User::factory()->create();
            expect($this->policy->view($semVinculo, $this->equipe))->toBeFalse();
        });

        it('usuário com vínculo em equipe diferente não pode ver esta equipe', function () {
            $outraEquipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
            $user = User::factory()->create();
            $user->equipes()->attach($outraEquipe->idt_equipe, [
                'papel' => PapelEquipe::MembroEquipe->value,
            ]);
            expect($this->policy->view($user, $this->equipe))->toBeFalse();
        });

    });

    // --- update ---

    describe('update()', function () {

        it('coord-equipe-h da equipe pode editá-la', function () {
            $coordH = User::factory()->create();
            $coordH->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordEquipeH->value,
            ]);
            $result = $this->policy->update($coordH, $this->equipe);
            expect($result->allowed())->toBeTrue();
        });

        it('coord-equipe-m da equipe pode editá-la', function () {
            $coordM = User::factory()->create();
            $coordM->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordEquipeM->value,
            ]);
            $result = $this->policy->update($coordM, $this->equipe);
            expect($result->allowed())->toBeTrue();
        });

        it('membro-equipe não pode editar a equipe', function () {
            $membro = User::factory()->create();
            $membro->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::MembroEquipe->value,
            ]);
            $result = $this->policy->update($membro, $this->equipe);
            expect($result->allowed())->toBeFalse()
                ->and($result->message())->toBe('Apenas coordenadores da equipe podem editá-la.');
        });

        it('usuário sem vínculo não pode editar', function () {
            $semVinculo = User::factory()->create();
            $result = $this->policy->update($semVinculo, $this->equipe);
            expect($result->allowed())->toBeFalse();
        });

    });

    // --- assignMembers ---

    describe('assignMembers()', function () {

        it('membro-equipe não pode atribuir membros', function () {
            $membro = User::factory()->create();
            $membro->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::MembroEquipe->value,
            ]);
            expect($this->policy->assignMembers($membro, $this->equipe))->toBeFalse();
        });

        it('coord-equipe-h não pode atribuir membros (apenas coord-geral via before())', function () {
            $coordH = User::factory()->create();
            $coordH->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordEquipeH->value,
            ]);
            expect($this->policy->assignMembers($coordH, $this->equipe))->toBeFalse();
        });

        it('usuário sem vínculo não pode atribuir membros', function () {
            $semVinculo = User::factory()->create();
            expect($this->policy->assignMembers($semVinculo, $this->equipe))->toBeFalse();
        });

        it('coord-geral pode atribuir membros (via before() no Gate, não via método direto)', function () {
            $coordGeral = User::factory()->create(['role' => 'user']);
            $coordGeral->equipes()->attach($this->equipe->idt_equipe, [
                'papel' => PapelEquipe::CoordGeral->value,
            ]);
            // Via Gate (inclui before()): deve ser true
            expect($coordGeral->can('assignMembers', $this->equipe))->toBeTrue();
            // Via método direto (não passa por before()): deve ser false
            expect($this->policy->assignMembers($coordGeral, $this->equipe))->toBeFalse();
        });

    });

});
