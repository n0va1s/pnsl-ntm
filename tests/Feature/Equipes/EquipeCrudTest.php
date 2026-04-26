<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->withoutVite();

    $this->vem = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );

    $this->outroMovimento = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'ECC'],
        ['nom_movimento' => 'Encontro de Casais com Cristo', 'dat_inicio' => '1980-01-01']
    );

    $this->equipe = Equipe::factory()->create([
        'idt_movimento' => $this->vem->idt_movimento,
        'nom_equipe' => 'Sala',
        'des_slug' => 'sala',
    ]);

    $this->equipeOutroMovimento = Equipe::factory()->create([
        'idt_movimento' => $this->outroMovimento->idt_movimento,
        'nom_equipe' => 'Equipe ECC',
        'des_slug' => 'equipe-ecc',
    ]);

    $this->coordGeral = User::factory()->create(['role' => 'user']);
    $this->coordGeral->equipes()->attach($this->equipe->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    $this->userSemVinculo = User::factory()->create(['role' => 'user']);
});

it('coord-geral acessa index e ve apenas equipes do seu movimento', function () {
    $this->actingAs($this->coordGeral)
        ->get(route('equipes.index'))
        ->assertOk()
        ->assertSee('Sala')
        ->assertDontSee('Equipe ECC');
});

it('usuario sem vinculo recebe 403 ao acessar create', function () {
    $this->actingAs($this->userSemVinculo)
        ->get(route('equipes.create'))
        ->assertForbidden();
});

it('usuario sem vinculo recebe 403 ao acessar edit', function () {
    $this->actingAs($this->userSemVinculo)
        ->get(route('equipes.edit', $this->equipe))
        ->assertForbidden();
});

it('coord-geral cria equipe via Volt', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.create')
        ->set('nom_equipe', 'Recepção')
        ->set('des_descricao', 'Equipe de acolhida')
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertRedirect(route('equipes.index'));

    $this->assertDatabaseHas('equipes', [
        'idt_movimento' => $this->vem->idt_movimento,
        'nom_equipe' => 'Recepção',
        'des_slug' => 'recepcao',
    ]);
});

it('slug duplicado no mesmo movimento retorna erro de validacao', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.create')
        ->set('nom_equipe', 'Outra Sala')
        ->set('des_slug', 'sala')
        ->call('salvar')
        ->assertHasErrors(['des_slug']);
});

it('coord-geral edita equipe via Volt', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.edit', ['equipe' => $this->equipe])
        ->set('nom_equipe', 'Sala Principal')
        ->set('des_slug', 'sala-principal')
        ->set('des_descricao', 'Equipe atualizada')
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertRedirect(route('equipes.index'));

    $this->assertDatabaseHas('equipes', [
        'idt_equipe' => $this->equipe->idt_equipe,
        'nom_equipe' => 'Sala Principal',
        'des_slug' => 'sala-principal',
        'des_descricao' => 'Equipe atualizada',
    ]);
});

it('coord-geral altera ind_ativa via edit', function () {
    $this->actingAs($this->coordGeral);

    Volt::test('equipes.edit', ['equipe' => $this->equipe])
        ->set('ind_ativa', false)
        ->call('salvar')
        ->assertHasNoErrors();

    expect($this->equipe->refresh()->ind_ativa)->toBeFalse();
});
