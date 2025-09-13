<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $perfis = User::orderBy('name', 'asc')->paginate(10);
        return view('configuracoes.RoleList', compact('perfis'));
    }


    public function change(Request $request)
    {
        $request->validate([
            'role' => 'required|array',
            'role.*' => 'in:admin,coord,user',
        ]);

        foreach ($request->input('role', []) as $userId => $role) {
            User::where('id', $userId)->update(['role' => $role]);
        }

        return redirect()->route('eventos.index')->with('success', 'Perfis atualizados com sucesso!');
    }
}
