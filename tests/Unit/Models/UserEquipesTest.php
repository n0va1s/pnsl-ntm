<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\Pessoa;
use App\Models\TipoMovimento;
use App\Models\User;

// Nota D-07: testes NÃO reutilizam createMovimentos() para equipes

describe('User::equipes() relacao', function () {
    beforeEach(function () {
        $this->vem = TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );
    });

    test('user pode ter multiplas equipes via pivot', function () {
        $user = User::factory()->create();
        $equipe1 = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $equipe2 = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

        $user->equipes()->attach($equipe1->idt_equipe, ['papel' => PapelEquipe::MembroEquipe->value]);
        $user->equipes()->attach($equipe2->idt_equipe, ['papel' => PapelEquipe::CoordGeral->value]);

        expect($user->equipes)->toHaveCount(2);
    });

    test('pivot traz papel como enum PapelEquipe', function () {
        $user = User::factory()->create();
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

        $user->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::MembroEquipe->value]);

        $primeiraEquipe = $user->equipes()->first();
        expect($primeiraEquipe->pivot->papel)->toBeInstanceOf(PapelEquipe::class);
        expect($primeiraEquipe->pivot->papel)->toBe(PapelEquipe::MembroEquipe);
    });

    test('vinculos soft-deleted nao aparecem em user->equipes', function () {
        $user = User::factory()->create();
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

        $user->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::MembroEquipe->value]);
        expect($user->equipes()->count())->toBe(1);

        // Soft-delete the pivot record directly
        EquipeUsuario::where('user_id', $user->id)
            ->where('idt_equipe', $equipe->idt_equipe)
            ->delete();

        // Reload relation - should be 0 now
        expect($user->fresh()->equipes()->count())->toBe(0);
    });

    test('attach cria registro no pivot com papel', function () {
        $user = User::factory()->create();
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

        $user->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::CoordEquipeH->value]);

        $this->assertDatabaseHas('equipe_usuario', [
            'user_id'    => $user->id,
            'idt_equipe' => $equipe->idt_equipe,
            'papel'      => 'coord_equipe_h',
        ]);
    });

    test('relacao nao quebra cascata User com Pessoa existente', function () {
        // Verificar que adicionar equipes nao interfere com a cascata User <-> Pessoa
        $user = User::factory()->create();
        // A cascata User->Pessoa e gerida no boot() do User — apos create, pessoa existe
        $pessoa = Pessoa::where('idt_usuario', $user->id)->first()
            ?? Pessoa::where('eml_pessoa', $user->email)->first();

        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $user->equipes()->attach($equipe->idt_equipe, ['papel' => PapelEquipe::MembroEquipe->value]);

        // Pessoa deve continuar existindo apos attach de equipe
        $pessoaAposAttach = Pessoa::where('idt_usuario', $user->id)->first()
            ?? Pessoa::where('eml_pessoa', $user->email)->first();
        expect($pessoaAposAttach)->not->toBeNull();

        // O vínculo de equipe deve existir
        expect($user->equipes()->count())->toBe(1);
    });
});
