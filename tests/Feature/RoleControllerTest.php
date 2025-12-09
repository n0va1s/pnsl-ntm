<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user']);
});

describe('RoleController', function () {
    test('admin can access role list', function () {
        $this->actingAs($this->admin)
            ->get(route('role.index'))
            ->assertStatus(200)
            ->assertViewIs('configuracoes.RoleList')
            ->assertViewHas('perfis');
    });

    test('non-admin cannot access role list', function () {
        $this->actingAs($this->user)
             ->get(route('role.index'))
             ->assertStatus(403);
    });

    test('admin can update roles', function () {
        $targetUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin)
            ->post(route('role.change'), [
                'role' => [
                    $targetUser->id => 'coord',
                ],
            ])
            ->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success');

        expect($targetUser->fresh()->role)->toBe('coord');
    });

    test('validates role input', function () {
        $this->actingAs($this->admin)
            ->post(route('role.change'), [
                'role' => [
                    $this->user->id => 'invalid_role',
                ],
            ])
            ->assertSessionHasErrors('role.*');
    });
});
