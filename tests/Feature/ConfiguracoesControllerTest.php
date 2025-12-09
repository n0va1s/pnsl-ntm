<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can access configuracoes index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('configuracoes.index'))
        ->assertStatus(200)
        ->assertViewIs('configuracoes.index');
});
