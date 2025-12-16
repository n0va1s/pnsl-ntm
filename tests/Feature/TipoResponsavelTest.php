<?php

use App\Models\TipoResponsavel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\CrudBasic;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class, CrudBasic::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
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

    $data = ['des_responsavel' => null];

    post(route('responsavel.store'), $data)
        ->assertSessionHasErrors('des_responsavel');

    $this->assertDatabaseCount('tipo_responsavel', 0);
});

describe('Responsavel::CRUD', function () {
    test('tipo responsavel respeita contrato basico', function () {
        $this->verificaOperacoes(
            TipoResponsavel::class,
            ['des_responsavel']
        );
    });

    test('tipo responsavel pode ser usado como foreign key', function () {
        $responsavel = TipoResponsavel::factory()->create();

        expect($responsavel->idt_responsavel)->toBeInt();
    });
});
