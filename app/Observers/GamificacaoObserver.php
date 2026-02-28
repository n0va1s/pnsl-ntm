<?php

namespace App\Observers;

use App\Models\Gamificacao;

class GamificacaoObserver
{
    // Sempre que um novo registo de pontos entrar na tabela 'gamificacao'
    public function created(Gamificacao $gamificacao): void
    {
        $gamificacao->pessoa->increment('qtd_pontos_total', $gamificacao->qtd_pontos);
    }

    // Se decidir remover um ponto (ex: cancelamento de inscrição)
    public function deleted(Gamificacao $gamificacao): void
    {
        $gamificacao->pessoa->decrement('qtd_pontos_total', $gamificacao->qtd_pontos);
    }
}
