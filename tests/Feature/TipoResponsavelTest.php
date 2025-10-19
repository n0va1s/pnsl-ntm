<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->user = createUser();
    $this->actingAs($this->user);
});

test('a pagina de listagem de tipo de responsavel esta acessivel', function () {

    $response = get(route('responsavel.index'));
    $response->assertStatus(200)
        ->assertViewIs('configuracoes.TipoResponsavelList');
});

test('a pagina de criacao de tipo de responsavel esta acessivel', function () {
    get(route('responsavel.create'))
        ->assertStatus(200);
});

test('pode criar um novo tipo de responsavel com sucesso', function () {

    $data = ['des_responsavel' => 'Novo Responsável'];

    post(route('responsavel.store'), $data)
        ->assertRedirect(route('responsavel.index'))
        ->assertSessionHas('success', 'Tipo de responsável adicionado com sucesso!');

    $this->assertDatabaseHas('tipo_responsavel', $data);
});

test('nao pode criar um tipo de responsavel sem a descricao', function () {

    $data = ['des_responsavel' => ''];

    post(route('responsavel.store'), $data)
        ->assertSessionHasErrors('des_responsavel');

    $this->assertDatabaseCount('tipo_responsavel', 0);
});
