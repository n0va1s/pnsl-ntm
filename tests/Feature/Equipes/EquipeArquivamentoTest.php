<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    $this->membro = User::factory()->create(['role' => 'user']);
    $this->membro->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::MembroEquipe->value,
    ]);
});

it('arquivar equipe aplica soft-delete preservando pivot equipe_usuario', function () {
    $this->actingAs($this->coordGeral);

    expect(DB::table('equipe_usuario')->where('idt_equipe', $this->equipe->idt_equipe)->count())
        ->toBe(2);

    Volt::test('equipes.index')
        ->call('arquivar', $this->equipe->idt_equipe)
        ->assertHasNoErrors();

    expect(Equipe::find($this->equipe->idt_equipe))->toBeNull();
    expect(Equipe::withTrashed()->find($this->equipe->idt_equipe)?->deleted_at)->not->toBeNull();

    expect(DB::table('equipe_usuario')->where('idt_equipe', $this->equipe->idt_equipe)->count())
        ->toBe(2);
});

it('equipe arquivada pode ser restaurada', function () {
    $this->actingAs($this->coordGeral);

    $this->equipe->delete();

    expect(Equipe::find($this->equipe->idt_equipe))->toBeNull();

    Volt::test('equipes.index')
        ->call('restaurar', $this->equipe->idt_equipe)
        ->assertHasNoErrors();

    expect(Equipe::find($this->equipe->idt_equipe))->not->toBeNull();
});
