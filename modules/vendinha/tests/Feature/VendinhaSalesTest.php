<?php

use App\Enums\PapelEquipe;
use App\Models\Equipe;
use App\Models\TipoMovimento;
use App\Models\User;
use Modules\Vendinha\Models\VendinhaProduto;
use Modules\Vendinha\Models\VendinhaVenda;

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

    $this->sala = Equipe::factory()->create([
        'idt_movimento' => $this->movimento->idt_movimento,
        'nom_equipe' => 'Sala',
        'des_slug' => 'sala-vendinha-test',
    ]);

    $this->coordVendinha = User::factory()->create(['role' => User::ROLE_USER]);
    $this->coordVendinha->equipes()->attach($this->vendinha->idt_equipe, [
        'papel' => PapelEquipe::CoordEquipeH->value,
    ]);
});

it('cadastra produto com custo real, valor de venda e estoque', function () {
    $this->actingAs($this->coordVendinha)
        ->post(route('vendinha.produtos.store'), [
            'nom_produto' => 'Terco',
            'vlr_custo' => '5.50',
            'vlr_venda' => '10.00',
            'qtd_estoque' => 12,
            'ind_ativo' => true,
        ])
        ->assertRedirect(route('vendinha.dashboard'));

    $this->assertDatabaseHas('vendinha_produtos', [
        'nom_produto' => 'Terco',
        'vlr_custo' => 5.50,
        'vlr_venda' => 10.00,
        'qtd_estoque' => 12,
    ]);
});

it('registra venda pendente, calcula lucro, baixa estoque e marca pagamento', function () {
    $produto = VendinhaProduto::create([
        'nom_produto' => 'Terco',
        'vlr_custo' => 5,
        'vlr_venda' => 12,
        'qtd_estoque' => 10,
        'ind_ativo' => true,
    ]);

    $comprador = User::factory()->create(['role' => User::ROLE_USER])->pessoa;

    $this->actingAs($this->coordVendinha)
        ->post(route('vendinha.vendas.store'), [
            'idt_pessoa' => $comprador->idt_pessoa,
            'idt_equipe' => $this->sala->idt_equipe,
            'status' => VendinhaVenda::STATUS_PENDENTE,
            'itens' => [
                ['produto_id' => $produto->id, 'quantidade' => 2],
            ],
        ])
        ->assertRedirect(route('vendinha.dashboard'));

    $venda = VendinhaVenda::with('itens')->firstOrFail();

    expect($venda->status)->toBe(VendinhaVenda::STATUS_PENDENTE)
        ->and((float) $venda->vlr_custo_total)->toBe(10.0)
        ->and((float) $venda->vlr_total)->toBe(24.0)
        ->and((float) $venda->vlr_lucro_total)->toBe(14.0)
        ->and($venda->idt_equipe)->toBe($this->sala->idt_equipe)
        ->and($venda->itens)->toHaveCount(1)
        ->and($produto->refresh()->qtd_estoque)->toBe(8);

    $this->actingAs($this->coordVendinha)
        ->post(route('vendinha.vendas.pagar', $venda))
        ->assertRedirect(route('vendinha.dashboard'));

    expect($venda->refresh()->status)->toBe(VendinhaVenda::STATUS_PAGO)
        ->and($venda->dat_pagamento)->not->toBeNull();
});

it('impede venda acima do estoque disponivel', function () {
    $produto = VendinhaProduto::create([
        'nom_produto' => 'Camisa',
        'vlr_custo' => 20,
        'vlr_venda' => 35,
        'qtd_estoque' => 1,
        'ind_ativo' => true,
    ]);

    $this->actingAs($this->coordVendinha)
        ->from(route('vendinha.vendas.create'))
        ->post(route('vendinha.vendas.store'), [
            'nom_comprador' => 'Comprador avulso',
            'status' => VendinhaVenda::STATUS_PAGO,
            'itens' => [
                ['produto_id' => $produto->id, 'quantidade' => 2],
            ],
        ])
        ->assertRedirect(route('vendinha.vendas.create'))
        ->assertSessionHasErrors('itens');

    expect($produto->refresh()->qtd_estoque)->toBe(1);
});
