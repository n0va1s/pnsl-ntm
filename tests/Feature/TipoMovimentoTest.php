<?php

use App\Models\TipoMovimento;
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
    // 1. Autenticação (se necessário para a rota)
    $this->actingAs($this->admin); // Adicione se a rota for protegida

    $data = [
        'dat_inicio' => '2024-01-01',
        // nom_movimento e des_sigla estão ausentes aqui
    ];

    post(route('movimento.store'), $data)
        // O status esperado de uma falha de validação é 302 (Redirecionar de Volta)
        ->assertStatus(302)

        // Esperamos erros para os campos que foram omitidos
        ->assertSessionHasErrors(['nom_movimento', 'des_sigla']);
    // Se você não enviou dat_inicio, adicione-o também:
    // ->assertSessionHasErrors(['nom_movimento', 'des_sigla', 'dat_inicio']);


    $this->assertDatabaseCount('tipo_movimento', 0);
});

describe('Movimento::CRUD', function () {
    test('tipo movimento respeita contrato basico', function () {
        $this->verificaOperacoes(
            TipoMovimento::class,
            ['nom_movimento']
        );
    });

    test('tipo movimento pode ser usado como foreign key', function () {
        $movimento = TipoMovimento::factory()->create();

        expect($movimento->idt_movimento)->toBeInt();
    });
});
