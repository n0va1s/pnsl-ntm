<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TipoPerfilController extends Controller
{
    use LogContext;

    public function index(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        Log::info('Requisição de listagem de perfis de usuário (RoleList) iniciada', $context);

        $perfis = User::orderBy('name', 'asc')->paginate(10);

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Listagem de perfis de usuário concluída com sucesso', array_merge($context, [
            'total_perfis' => $perfis->total(),
            'duration_ms' => $duration,
        ]));

        return view('configuracoes.TipoPerfilList', compact('perfis'));
    }

    public function change(Request $request)
    {
        $start = microtime(true);
        $context = $this->getLogContext($request);

        $rolesData = $request->input('role', []);

        Log::info('Tentativa de atualização de perfis (roles) de usuário', array_merge($context, [
            'total_perfis_enviados' => count($rolesData),
        ]));

        $request->validate([
            'role' => 'required|array',
            'role.*' => 'in:admin,coord,user',
        ]);

        foreach ($request->input('role', []) as $userId => $role) {
            User::where('id', $userId)->update(['role' => $role]);
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        Log::notice('Perfis de usuário atualizados com sucesso', array_merge($context, [
            'perfis_atualizados' => count($rolesData),
            'duration_ms' => $duration,
        ]));

        return redirect()->route('eventos.index')->with('success', 'Perfis atualizados com sucesso!');
    }
}
