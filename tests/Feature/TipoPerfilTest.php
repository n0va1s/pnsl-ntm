<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\CrudBasic;

uses(RefreshDatabase::class, CrudBasic::class);

/*
|--------------------------------------------------------------------------
| SETUP GLOBAL
|--------------------------------------------------------------------------
*/
beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->user = User::factory()->create([
        'role' => 'user',
    ]);
});

/*
|--------------------------------------------------------------------------
| LISTAGEM DE PERFIS
|--------------------------------------------------------------------------
*/
describe('TipoPerfilController::index', function () {

    test('admin consegue acessar a lista de perfis', function () {
        $this->actingAs($this->admin)
            ->get(route('role.index'))
            ->assertStatus(200)
            ->assertViewIs('configuracoes.TipoPerfilList')
            ->assertViewHas('perfis');
    });

    test('usuario comum nao pode acessar a lista de perfis', function () {
        $this->actingAs($this->user)
            ->get(route('role.index'))
            ->assertForbidden();
    });
});

/*
|--------------------------------------------------------------------------
| ALTERAÇÃO DE PERFIL
|--------------------------------------------------------------------------
*/
describe('TipoPerfilController::change', function () {

    test('admin consegue alterar o perfil de um usuario', function () {
        $usuarioAlvo = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($this->admin)
            ->post(route('role.change'), [
                'role' => [
                    $usuarioAlvo->id => 'coord',
                ],
            ])
            ->assertRedirect(route('eventos.index'))
            ->assertSessionHas('success');

        expect($usuarioAlvo->fresh()->role)->toBe('coord');
    });

    test('nao permite alterar para um perfil invalido', function () {
        $this->actingAs($this->admin)
            ->post(route('role.change'), [
                'role' => [
                    $this->user->id => 'perfil_invalido',
                ],
            ])
            ->assertSessionHasErrors('role.*');
    });
});

describe('User id', function () {
    test('id pode ser usado como foreign key', function () {
        $user = User::factory()->create();

        expect($user->id)->toBeInt();
    });
});
