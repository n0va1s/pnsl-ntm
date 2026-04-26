<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\TipoMovimento;
use App\Models\User;

describe('EquipeUsuario pivot model', function () {
    beforeEach(function () {
        $this->vem = TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );
        $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $this->membro = User::factory()->create();
    });

    test('cast do papel resolve para enum PapelEquipe', function () {
        $ev = EquipeUsuario::create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        $fresh = EquipeUsuario::find($ev->idt_equipe_usuario);
        expect($fresh->papel)->toBeInstanceOf(PapelEquipe::class);
        expect($fresh->papel)->toBe(PapelEquipe::MembroEquipe);
    });

    test('creating sem auth mantem usr_inclusao nulo', function () {
        // Sem usuario autenticado, usr_inclusao deve permanecer null
        auth()->logout();

        $ev = EquipeUsuario::create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        expect($ev->usr_inclusao)->toBeNull();
        expect($ev->dat_inclusao)->not->toBeNull(); // dat_inclusao sempre preenchida
    });

    test('creating com auth preenche usr_inclusao e dat_inclusao', function () {
        $autor = User::factory()->create();
        $this->actingAs($autor);

        $ev = EquipeUsuario::create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        expect($ev->usr_inclusao)->toBe($autor->id);
        expect($ev->dat_inclusao)->not->toBeNull();
    });

    test('updating com auth preenche usr_alteracao e dat_alteracao mantendo usr_inclusao intacto', function () {
        $autor = User::factory()->create();
        $this->actingAs($autor);

        $ev = EquipeUsuario::create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        $idInclusao = $ev->usr_inclusao;

        $alterador = User::factory()->create();
        $this->actingAs($alterador);

        $ev->papel = PapelEquipe::CoordGeral;
        $ev->save();

        $fresh = EquipeUsuario::find($ev->idt_equipe_usuario);
        expect($fresh->usr_inclusao)->toBe($idInclusao); // intacto
        expect($fresh->usr_alteracao)->toBe($alterador->id);
        expect($fresh->dat_alteracao)->not->toBeNull();
    });

    test('SoftDeletes preserva o registro com deleted_at', function () {
        $ev = EquipeUsuario::create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
            'papel' => PapelEquipe::MembroEquipe->value,
        ]);

        $id = $ev->idt_equipe_usuario;
        $ev->delete();

        expect(EquipeUsuario::find($id))->toBeNull();
        expect(EquipeUsuario::withTrashed()->find($id))->not->toBeNull();
        expect(EquipeUsuario::withTrashed()->find($id)->deleted_at)->not->toBeNull();
    });

    test('incrementing e true e primary key e idt_equipe_usuario', function () {
        $ev = new EquipeUsuario;
        expect($ev->getIncrementing())->toBeTrue();
        expect($ev->getKeyName())->toBe('idt_equipe_usuario');
    });

    test('timestamps public prop e false', function () {
        $ev = new EquipeUsuario;
        expect($ev->timestamps)->toBeFalse();
    });
});
