<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $perfis = User::all();
        return view('configuracoes.RoleList', compact('perfis'));
    }


    public function change(Request $request)
    {
        try {
            foreach ($request->input('role', []) as $userId => $role) {
                User::where('id', $userId)->update(['role' => $role]);
            }
            return redirect()->route('eventos.index')->with('success', 'Perfis atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar perfis: ' . $e->getMessage());
        }
    }
}
