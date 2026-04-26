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

    $this->equipe = Equipe::factory()->create(['idt_movimento' => $this->vem->idt_movimento]);

    $this->coordGeral = User::factory()->create(['role' => 'user']);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);
});

it('preenche auditoria ao criar atualizar e remover vinculo', function () {
    $this->actingAs($this->coordGeral);

    $usuario = User::factory()->create(['role' => 'user']);
    $usuario->pessoa()->update(['tip_genero' => 'M']);

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->set('userId', $usuario->id)
        ->set('papel', PapelEquipe::MembroEquipe->value)
        ->call('atribuir')
        ->assertHasNoErrors();

    $vinculo = EquipeUsuario::where('idt_equipe', $this->equipe->idt_equipe)
        ->where('user_id', $usuario->id)
        ->firstOrFail();

    expect($vinculo->usr_inclusao)->toBe($this->coordGeral->id)
        ->and($vinculo->dat_inclusao)->not->toBeNull()
        ->and($vinculo->usr_alteracao)->toBeNull();

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->call('alterarPapel', $vinculo->idt_equipe_usuario, PapelEquipe::CoordEquipeH->value)
        ->assertHasNoErrors();

    $vinculo->refresh();

    expect($vinculo->usr_alteracao)->toBe($this->coordGeral->id)
        ->and($vinculo->dat_alteracao)->not->toBeNull();

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->call('remover', $vinculo->idt_equipe_usuario)
        ->assertHasNoErrors();

    $removido = EquipeUsuario::withTrashed()->findOrFail($vinculo->idt_equipe_usuario);

    expect($removido->deleted_at)->not->toBeNull()
        ->and($removido->usr_alteracao)->toBe($this->coordGeral->id)
        ->and($removido->dat_alteracao)->not->toBeNull();
});

it('perfil exibe equipes ativas do usuario com papel', function () {
    $usuario = User::factory()->create(['role' => 'user']);
    $usuario->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::MembroEquipe->value,
    ]);

    $this->actingAs($usuario)
        ->get(route('settings.profile'))
        ->assertOk()
        ->assertSee('Equipes')
        ->assertSee($this->equipe->nom_equipe)
        ->assertSee(PapelEquipe::MembroEquipe->label());
});
