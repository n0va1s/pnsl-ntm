<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContatoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $contatos = Contato::with(['movimento'])
            ->when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->select(
                'idt_contato',
                'nom_contato',
                'eml_contato',
                'tel_contato',
                'txt_mensagem',
                'idt_movimento'
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'contato.list',
            compact(
                'contatos',
                'search'
            )
        );
    }

    public function destroy($id): RedirectResponse
    {
        $contato = Contato::findOrFail($id);

        try {
            $contato->delete();
            return redirect()->route('contatos.index')
                ->with('success', 'Contato resolvido com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->route('contatos.index')
                ->with('error', 'Erro ao resolver o contato. Verifique se há vínculos.');
        }
    }
}
