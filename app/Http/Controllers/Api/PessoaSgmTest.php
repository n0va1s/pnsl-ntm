<?php

use App\Models\Ficha;
use App\Models\FichaSGM;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lista apenas pessoas com vinculo ao segue-me na rota api/pessoas-sgm', function () {
    // 1. Cria uma pessoa qualquer (não deve aparecer na lista do SGM)
    $pessoaNormal = Pessoa::factory()->create();

    // 2. Cria uma pessoa com a Ficha e a FichaSGM vinculadas
    $pessoaSgm = Pessoa::factory()->create();

    $ficha = Ficha::factory()->create([
        'idt_pessoa' => $pessoaSgm->idt_pessoa
    ]);

    FichaSGM::factory()->create([
        'idt_ficha' => $ficha->idt_ficha
    ]);

    // 3. Testa o Endpoint
    $response = $this->getJson('/api/pessoas-sgm');

    // 4. Validação: Checa se a pessoa do SGM está no JSON e se a Normal NÃO está
    $response->assertStatus(200)
        ->assertJsonFragment(['pessoa_id' => $pessoaSgm->idt_pessoa])
        ->assertJsonMissing(['pessoa_id' => $pessoaNormal->idt_pessoa]);
});

test('lista apenas candidatos na rota api/pessoas-sgm/candidatos', function () {
    // Pessoa com usuário
    $pessoaUsuario = Pessoa::factory()->create(['idt_usuario' => 1]);
    $ficha1 = Ficha::factory()->create(['idt_pessoa' => $pessoaUsuario->idt_pessoa]);
    FichaSGM::factory()->create(['idt_ficha' => $ficha1->idt_ficha]);

    // Pessoa candidato (sem usuário)
    $pessoaCandidato = Pessoa::factory()->create(['idt_usuario' => null]);
    $ficha2 = Ficha::factory()->create(['idt_pessoa' => $pessoaCandidato->idt_pessoa]);
    FichaSGM::factory()->create(['idt_ficha' => $ficha2->idt_ficha]);

    $response = $this->getJson('/api/pessoas-sgm/candidatos');

    $response->assertStatus(200)
        ->assertJsonFragment(['pessoa_id' => $pessoaCandidato->idt_pessoa])
        ->assertJsonMissing(['pessoa_id' => $pessoaUsuario->idt_pessoa]);
});

test('lista apenas usuarios na rota api/pessoas-sgm/usuarios', function () {
    // Pessoa com usuário
    $pessoaUsuario = Pessoa::factory()->create(['idt_usuario' => 1]);
    $ficha1 = Ficha::factory()->create(['idt_pessoa' => $pessoaUsuario->idt_pessoa]);
    FichaSGM::factory()->create(['idt_ficha' => $ficha1->idt_ficha]);

    // Pessoa candidato (sem usuário)
    $pessoaCandidato = Pessoa::factory()->create(['idt_usuario' => null]);
    $ficha2 = Ficha::factory()->create(['idt_pessoa' => $pessoaCandidato->idt_pessoa]);
    FichaSGM::factory()->create(['idt_ficha' => $ficha2->idt_ficha]);

    $response = $this->getJson('/api/pessoas-sgm/usuarios');

    $response->assertStatus(200)
        ->assertJsonFragment(['pessoa_id' => $pessoaUsuario->idt_pessoa])
        ->assertJsonMissing(['pessoa_id' => $pessoaCandidato->idt_pessoa]);
});

test('retorna uma pessoa especifica do segue-me na rota api/pessoas-sgm/{id}', function () {
    // Prepara os dados
    $pessoaSgm = Pessoa::factory()->create();
    $ficha = Ficha::factory()->create(['idt_pessoa' => $pessoaSgm->idt_pessoa]);
    FichaSGM::factory()->create(['idt_ficha' => $ficha->idt_ficha]);

    // Realiza a chamada passando o ID
    $response = $this->getJson('/api/pessoas-sgm/' . $pessoaSgm->idt_pessoa);

    // Deve retornar 200 OK e possuir os dados do JSON mapeados na PessoaResource
    $response->assertStatus(200)
        ->assertJsonFragment([
            'pessoa_id' => $pessoaSgm->idt_pessoa,
            'nome' => $pessoaSgm->nom_pessoa
        ]);
});

test('retorna 404 se tentar acessar pessoa que existe mas nao pertence ao segue-me', function () {
    // Uma pessoa no banco, mas que nunca fez o Segue-me
    $pessoaNormal = Pessoa::factory()->create();

    // Realiza a chamada
    $response = $this->getJson('/api/pessoas-sgm/' . $pessoaNormal->idt_pessoa);

    // Deve retornar um erro de Not Found porque ela falhou na query do whereHas('fichas')
    $response->assertStatus(404);
});
