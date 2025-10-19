<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);
});

test('a pagina de listagem de tipo de movimento esta acessivel', function () {

    $response = get(route('movimento.index'));
    $response->assertStatus(200)
        ->assertViewIs('configuracoes.TipoMovimentoList');
});

test('a pagina de criacao de tipo de movimento esta acessivel', function () {
    get(route('movimento.create'))
        ->assertStatus(200);
});

test('pode criar um novo tipo de movimento com sucesso', function () {

    $data = [
        'nom_movimento' => 'Movimento de Entrada',
        'des_sigla' => 'ME',
        'dat_inicio' => '2024-01-01 00:00:00',
    ];

    post(route('movimento.store'), $data)
        ->assertRedirect(route('movimento.index'))
        ->assertSessionHas('success', 'Movimento criado com sucesso!');

    $this->assertDatabaseHas('tipo_movimento', $data);
});

test('nao pode criar um tipo de movimento sem a descricao', function () {

    $data = [
        'nom_movimento' => '',
        'des_sigla' => '',
        'dat_inicio' => '2024-01-01',
    ];

    post(route('movimento.store'), $data)
        ->assertSessionHasErrors(['nom_movimento', 'des_sigla']);

    $this->assertDatabaseCount('tipo_movimento', 0);
});
