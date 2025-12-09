<?php

beforeEach(function () {
    $this->user = createUser();
});

test('visitante sao direcionados pra pagina de login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('usuario autenticados acessam o dashboard', function () {
    $this->actingAs($this->user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
