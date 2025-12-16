<?php

use App\Models\TipoSituacao;
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
    $data = ['des_situacao' => null];

    post(route('situacao.store'), $data)
        ->assertSessionHasErrors('des_situacao');

    $this->assertDatabaseCount('tipo_situacao', 0);
});

describe('Situacao::CRUD', function () {
    test('tipo situacao respeita contrato basico', function () {
        $this->verificaOperacoes(
            TipoSituacao::class,
            ['des_situacao']
        );
    });

    test('tipo situacao pode ser usado como foreign key', function () {
        $situacao = TipoSituacao::factory()->create();

        expect($situacao->idt_situacao)->toBeInt();
    });
});
