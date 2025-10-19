<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);
});

test('shows the tiporesponsavel list page', function () {
    $response = $this->get('/configuracoes/situacao');

    $response->assertStatus(200);
    $response->assertViewIs('configuracoes.TipoSituacaoList');
});

test('a pagina de criacao de tipo de situacao esta acessivel', function () {
    get(route('situacao.create'))
        ->assertStatus(200);
});

test('pode criar um novo tipo de situacao com sucesso', function () {
    $data = ['des_situacao' => 'Nova Situação'];

    post(route('situacao.store'), $data)
        ->assertRedirect(route('situacao.index'))
        ->assertSessionHas('success', 'Tipo de situação adicionado com sucesso!');

    $this->assertDatabaseHas('tipo_situacao', $data);
});

test('nao pode criar um tipo de situacao sem a descricao', function () {
    $data = ['des_situacao' => ''];

    post(route('situacao.store'), $data)
        ->assertSessionHasErrors('des_situacao');

    $this->assertDatabaseCount('tipo_situacao', 0);
});
