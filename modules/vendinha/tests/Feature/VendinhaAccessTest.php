<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;

beforeEach(function () {
    $this->movimento = TipoMovimento::firstOrCreate(
        ['des_sigla' => 'VEM'],
        ['nom_movimento' => 'Encontro de Adolescentes com Cristo', 'dat_inicio' => '2000-07-01']
    );

    $this->vendinha = Equipe::factory()->create([
        'idt_movimento' => $this->movimento->idt_movimento,
        'nom_equipe' => 'Vendinha',
        'des_slug' => 'vendinha',
    ]);
});

it('permite acesso para admin, coordenacao geral e coordenador da vendinha', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $coordGeral = User::factory()->create(['role' => User::ROLE_USER]);
    $coordGeral->equipes()->attach($this->vendinha->idt_equipe, [
        'papel' => PapelEquipe::CoordGeral->value,
    ]);

    $coordVendinha = User::factory()->create(['role' => User::ROLE_USER]);
    $coordVendinha->equipes()->attach($this->vendinha->idt_equipe, [
        'papel' => PapelEquipe::CoordEquipeH->value,
    ]);

    $this->actingAs($admin)->get(route('vendinha.dashboard'))->assertOk();
    $this->actingAs($coordGeral)->get(route('vendinha.dashboard'))->assertOk();
    $this->actingAs($coordVendinha)->get(route('vendinha.dashboard'))->assertOk();
});

it('bloqueia usuario sem permissao da vendinha', function () {
    $usuario = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($usuario)
        ->get(route('vendinha.dashboard'))
        ->assertForbidden();
});

it('renderiza formularios principais para coordenador da vendinha', function () {
    $coordVendinha = User::factory()->create(['role' => User::ROLE_USER]);
    $coordVendinha->equipes()->attach($this->vendinha->idt_equipe, [
        'papel' => PapelEquipe::CoordEquipeH->value,
    ]);

    $this->actingAs($coordVendinha)
        ->get(route('vendinha.produtos.create'))
        ->assertOk()
        ->assertSee('Novo produto');

    $this->actingAs($coordVendinha)
        ->get(route('vendinha.vendas.create'))
        ->assertOk()
        ->assertSee('Nova venda');
});
