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

it('bloqueia segundo coordenador H na mesma equipe', function () {
    $this->actingAs($this->coordGeral);

    $coordAtual = User::factory()->create(['role' => 'user']);
    $coordAtual->pessoa()->update(['tip_genero' => 'M']);

    $novoCoord = User::factory()->create(['role' => 'user']);
    $novoCoord->pessoa()->update(['tip_genero' => 'M']);

    EquipeUsuario::factory()
        ->comoPapel(PapelEquipe::CoordEquipeH)
        ->create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $coordAtual->id,
        ]);

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->set('userId', $novoCoord->id)
        ->set('papel', PapelEquipe::CoordEquipeH->value)
        ->call('atribuir')
        ->assertHasErrors(['papel']);

    expect(EquipeUsuario::where('idt_equipe', $this->equipe->idt_equipe)
        ->where('papel', PapelEquipe::CoordEquipeH->value)
        ->count())->toBe(1);
});

it('bloqueia segundo coordenador M ao alterar papel', function () {
    $this->actingAs($this->coordGeral);

    $coordAtual = User::factory()->create(['role' => 'user']);
    $coordAtual->pessoa()->update(['tip_genero' => 'F']);

    $membro = User::factory()->create(['role' => 'user']);
    $membro->pessoa()->update(['tip_genero' => 'F']);

    EquipeUsuario::factory()
        ->comoPapel(PapelEquipe::CoordEquipeM)
        ->create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $coordAtual->id,
        ]);

    $vinculoMembro = EquipeUsuario::factory()
        ->comoPapel(PapelEquipe::MembroEquipe)
        ->create([
            'idt_equipe' => $this->equipe->idt_equipe,
            'user_id' => $membro->id,
        ]);

    Volt::test('equipes.atribuir', ['equipe' => $this->equipe])
        ->call('alterarPapel', $vinculoMembro->idt_equipe_usuario, PapelEquipe::CoordEquipeM->value)
        ->assertHasErrors(['papel']);

    expect($vinculoMembro->refresh()->papel)->toBe(PapelEquipe::MembroEquipe);
});
