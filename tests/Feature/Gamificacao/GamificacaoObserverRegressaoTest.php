<?php

use App\Models\Gamificacao;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('gamificacao observer incrementa e decrementa pontuacao total da pessoa', function () {
    $pessoa = Pessoa::factory()->create(['qtd_pontos_total' => 10]);

    $gamificacao = Gamificacao::create([
        'idt_pessoa' => $pessoa->idt_pessoa,
        'qtd_pontos' => 25,
        'des_motivo' => 'Regressao observer',
    ]);

    expect($pessoa->refresh()->qtd_pontos_total)->toBe(35);

    $gamificacao->delete();

    expect($pessoa->refresh()->qtd_pontos_total)->toBe(10);
});
