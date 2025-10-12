<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait LogContext
{
    /**
     * Obtém o contexto base para logs.
     * Deve ser chamado dentro de um método de Controller que injeta Request.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @return array
     */
    protected function getLogContext(Request $request): array
    {
        return [
            'user_id' => auth()->check() ? auth()->id() : 'guest',
            'ip' => $request->ip(),
            'route_name' => $request->route() ? $request->route()->getName() : null,
            // trace_id é automático via Log::withContext()
        ];
    }
}
