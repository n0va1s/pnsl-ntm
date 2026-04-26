<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('retorna 404 silencioso quando o addon esta desativado', function () {
    config(['fitness-challenge.enabled' => false]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('desafios.challenges.index'))
        ->assertNotFound();
});

it('permite acessar rotas quando o addon esta ativado', function () {
    config(['fitness-challenge.enabled' => true]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('desafios.challenges.index'))
        ->assertOk()
        ->assertJson(['data' => []]);
});
