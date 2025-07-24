<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class PerfilUsuarioController extends Controller
{
    public function index()
    {
        $perfis = User::all();
        return view('configuracoes.PerfilUsuarioList', compact('perfis'));
    }


    public function change(Request $request)
    {
        try {
            foreach ($request->input('role', []) as $userId => $role) {
                User::where('id', $userId)->update(['role' => $role]);
            }
            return redirect()->back()->with('success', 'Perfis atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar perfis: ' . $e->getMessage());
        }
    }
}
