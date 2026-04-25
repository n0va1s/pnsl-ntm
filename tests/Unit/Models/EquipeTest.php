<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\TipoMovimento;
use App\Models\User;
use Illuminate\Support\Str;

// Nota D-07: testes desta fase NÃO reutilizam createMovimentos() para equipes
// (createMovimentos popula tipo_equipe legado, não a tabela equipes nova)

describe('Equipe model', function () {
    beforeEach(function () {
        // Criar TipoMovimento VEM para os testes
        $this->vem = TipoMovimento::firstOrCreate(
            ['des_sigla' => 'VEM'],
            ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
        );
    });

    test('escopo ativas filtra ind_ativa = true', function () {
        Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento, 'ind_ativa' => true]);
        Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento, 'ind_ativa' => false]);

        $ativas = Equipe::ativas()->get();
        expect($ativas)->toHaveCount(1);
        expect($ativas->first()->ind_ativa)->toBeTrue();
    });

    test('escopo paraMovimento filtra por idt_movimento', function () {
        $ecc = TipoMovimento::firstOrCreate(
            ['des_sigla' => 'ECC'],
            ['nom_movimento' => 'Encontro de Casais com Cristo', 'dat_inicio' => '1980-01-01']
        );

        Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        Equipe::factory()->create(['idt_movimento' => $ecc->idt_movimento]);

        $equipesVEM = Equipe::paraMovimento($this->vem->idt_movimento)->get();
        expect($equipesVEM)->toHaveCount(1);
        expect($equipesVEM->first()->idt_movimento)->toBe($this->vem->idt_movimento);
    });

    test('mutator de nom_equipe gera des_slug via Str::slug', function () {
        $equipe = Equipe::factory()->create([
            'idt_movimento' => $this->vem->idt_movimento,
            'nom_equipe' => 'Troca de Ideias',
            'des_slug' => 'troca-de-ideias',
        ]);

        expect($equipe->des_slug)->toBe('troca-de-ideias');
    });

    test('slug trata acentos pt_BR', function () {
        expect(Str::slug('Emaús'))->toBe('emaus');
        expect(Str::slug('Oração'))->toBe('oracao');
        expect(Str::slug('Troca de Ideias'))->toBe('troca-de-ideias');
        expect(Str::slug('Recepção'))->toBe('recepcao');
        expect(Str::slug('Alimentação'))->toBe('alimentacao');
    });

    test('mutator de nom_equipe seta des_slug automaticamente', function () {
        $equipe = new Equipe;
        $equipe->idt_movimento = $this->vem->idt_movimento;
        $equipe->nom_equipe = 'Emaús';
        $equipe->save();

        expect($equipe->des_slug)->toBe('emaus');
    });

    test('relacao movimento retorna TipoMovimento', function () {
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

        expect($equipe->movimento)->toBeInstanceOf(TipoMovimento::class);
        expect($equipe->movimento->des_sigla)->toBe('VEM');
    });

    test('relacao usuarios traz papel e colunas de auditoria no pivot', function () {
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $user = User::factory()->create();

        $equipe->usuarios()->attach($user->id, ['papel' => PapelEquipe::MembroEquipe->value]);

        $usuario = $equipe->usuarios()->first();
        expect($usuario)->toBeInstanceOf(User::class);
        // pivot->papel é castado para enum pelo EquipeUsuario model
        expect($usuario->pivot->papel)->toBe(PapelEquipe::MembroEquipe);
    });

    test('coordenadores retorna somente papeis diferentes de membro_equipe', function () {
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $coordGeral = User::factory()->create();
        $coordH = User::factory()->create();
        $membro = User::factory()->create();

        $equipe->usuarios()->attach($coordGeral->id, ['papel' => PapelEquipe::CoordGeral->value]);
        $equipe->usuarios()->attach($coordH->id, ['papel' => PapelEquipe::CoordEquipeH->value]);
        $equipe->usuarios()->attach($membro->id, ['papel' => PapelEquipe::MembroEquipe->value]);

        $coordenadores = $equipe->coordenadores()->get();
        expect($coordenadores)->toHaveCount(2);
        // pivot->papel é castado para enum; comparar com o enum (não o valor string)
        $papeis = $coordenadores->pluck('pivot.papel')->toArray();
        expect($papeis)->not->toContain(PapelEquipe::MembroEquipe);
    });

    test('membros retorna somente membro_equipe', function () {
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $coord = User::factory()->create();
        $membro = User::factory()->create();

        $equipe->usuarios()->attach($coord->id, ['papel' => PapelEquipe::CoordGeral->value]);
        $equipe->usuarios()->attach($membro->id, ['papel' => PapelEquipe::MembroEquipe->value]);

        $membros = $equipe->membros()->get();
        expect($membros)->toHaveCount(1);
        // pivot->papel é castado para enum pelo EquipeUsuario model
        expect($membros->first()->pivot->papel)->toBe(PapelEquipe::MembroEquipe);
    });

    test('SoftDeletes preserva o registro', function () {
        $equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);
        $id = $equipe->idt_equipe;

        $equipe->delete();

        expect(Equipe::find($id))->toBeNull();
        expect(Equipe::withTrashed()->find($id))->not->toBeNull();
        expect(Equipe::withTrashed()->find($id)->deleted_at)->not->toBeNull();
    });
});
