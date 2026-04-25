<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\EquipeUsuario;
use App\Models\TipoMovimento;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->withoutVite();

    $this->vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );

    $this->equipe = Equipe::factory()->create([
        'idt_movimento' => $this->vem->idt_movimento,
        'nom_equipe' => 'Sala',
    ]);

    $this->coordGeral = User::factory()->create(['role' => 'user']);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    $this->membro = User::factory()->create(['role' => 'user']);
    $this->membro->pessoa()->update(['tip_genero' => 'M']);
});

it('coord-geral acessa tela de atribuicao e usuario comum recebe 403', function () {
    $this->actingAs($this->coordGeral)
        ->get(route('equipes.atribuir', $this->equipe))
        ->assertOk()
        ->assertSee('Gerenciar membros');

    $this->actingAs($this->membro)
        ->get(route('equipes.atribuir', $this->equipe))
        ->assertForbidden();
});

it('coord-geral atribui membro e remove preservando historico por soft-delete', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->set('userId', $this->membro->id)
        ->set('papel', PapelEquipe::MembroEquipe->value)
        ->call('atribuir')
        ->assertHasNoErrors();

    $vinculo = EquipeUsuario::where('idt_equipe', $this->equipe->idt_equipe)
        ->where('user_id', $this->membro->id)
        ->first();

    expect($vinculo)->not->toBeNull()
        ->and($vinculo->papel)->toBe(PapelEquipe::MembroEquipe)
        ->and($vinculo->usr_inclusao)->toBe($this->coordGeral->id)
        ->and($vinculo->dat_inclusao)->not->toBeNull();

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->call('remover', $vinculo->idt_equipe_usuario)
        ->assertHasNoErrors();

    expect(EquipeUsuario::find($vinculo->idt_equipe_usuario))->toBeNull();
    expect(EquipeUsuario::withTrashed()->find($vinculo->idt_equipe_usuario)?->deleted_at)->not->toBeNull();
});

it('coord-geral altera papel de vinculo ativo', function () {
    $this->actingAs($this->coordGeral);

    $vinculo = EquipeUsuario::factory()
        ->comoPapel(PapelEquipe::MembroEquipe)
        ->create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $this->membro->id,
        ]);

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->call('alterarPapel', $vinculo->idt_equipe_usuario, PapelEquipe::CoordEquipeH->value)
        ->assertHasNoErrors();

    $vinculo->refresh();

    expect($vinculo->papel)->toBe(PapelEquipe::CoordEquipeH)
        ->and($vinculo->usr_alteracao)->toBe($this->coordGeral->id)
        ->and($vinculo->dat_alteracao)->not->toBeNull();
});
