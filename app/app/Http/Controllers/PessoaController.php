<?php

namespace App\Http\Controllers;

use App\Http\Requests\PessoaRequest;
use App\Models\Pessoa;
use App\Models\TipoRestricao;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PessoaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $pessoas = Pessoa::with(['foto', 'usuario', 'saude'])
            ->when($search, function ($query, $search) {
                return $query->where('nom_pessoa', 'like', "%{$search}%")
                    ->orWhere('nom_apelido', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pessoa.list', compact('pessoas', 'search'));
    }

    public function create(): View
    {
        $pessoa = new Pessoa();
        $restricoes = TipoRestricao::select(
            'idt_restricao',
            'tip_restricao',
            'des_restricao',
            'txt_restricao'
        )->get();

        return view('pessoa.form', [
            'pessoa' => $pessoa,
            'restricoes' => $restricoes,
        ]);
    }

    public function store(PessoaRequest $request): RedirectResponse
    {
        // Pega o ID do usuário conforme o email informado no cadastro da pessoa
        $data = $request->validated();
        $user = UserService::getUsuarioByEmail($request->input('eml_pessoa'));
        if ($user) {
            $data['idt_usuario'] = $user->id;
        }
        $pessoa = Pessoa::create($data);

        // Foto
        if ($request->hasFile('med_foto')) {
            $arquivo = $request->file('med_foto');
            $caminho = $arquivo->store('fotos/pessoa/', 'public'); // pasta 'storage/app/public/fotos'

            if ($pessoa->foto) {
                $pessoa->foto()->update(['med_foto' => $caminho]);
            } else {
                $pessoa->foto()->create(['med_foto' => $caminho]);
            }
        }

        // Saude
        if ($request->input('ind_restricao') == 1) {
            foreach ($request->input('restricoes', []) as $idt_restricao => $value) {
                if ($value) {
                    $pessoa->saude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $request->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        return redirect()->route('pessoas.index')->with('success', 'Pessoa criada com sucesso.');
    }

    public function show($id): View
    {
        $pessoa = Pessoa::with(['foto', 'usuario', 'saude'])->find($id);
        return view('pessoa.form', compact('pessoa'));
    }

    public function edit($id): View
    {
        $pessoa = Pessoa::with(['foto', 'usuario', 'saude'])->find($id);
        $restricoes = TipoRestricao::select(
            'idt_restricao',
            'tip_restricao',
            'des_restricao',
            'txt_restricao'
        )->get();

        return view('pessoa.form', [
            'pessoa' => $pessoa,
            'restricoes' => $restricoes,
        ]);
    }

    public function update(PessoaRequest $request, $id): RedirectResponse
    {
        $pessoa = Pessoa::with(['foto', 'usuario', 'saude'])->findOrFail($id);
        $user = UserService::getUsuarioByEmail($request->input('eml_pessoa'));
        $data = $request->validated();
        if ($user) {
            $data['idt_usuario'] = $user->id;
        }
        $pessoa->update($data);

        // Foto
        if ($request->hasFile('med_foto')) {
            $arquivo = $request->file('med_foto');
            $caminho = $arquivo->store('fotos/pessoa/', 'public'); // pasta 'storage/app/public/fotos/pessoa/'

            if ($pessoa->foto) {
                $pessoa->foto()->update(['med_foto' => $caminho]);
            } else {
                $pessoa->foto()->create(['med_foto' => $caminho]);
            }
        }


        // Saude
        $pessoa->saude()->delete();
        if ($request->input('ind_restricao') == 1) {
            foreach ($request->input('restricoes', []) as $idt_restricao => $value) {
                if ($value) {
                    $pessoa->saude()->create([
                        'idt_restricao' => $idt_restricao,
                        'txt_complemento' => $request->input("complementos.$idt_restricao"),
                    ]);
                }
            }
        }

        return redirect()->route('pessoas.index')->with('success', 'Pessoa atualizada com sucesso.');
    }

    public function destroy($id): RedirectResponse
    {
        try {
            // Cascade
            Pessoa::find($id)->delete();

            return redirect()
                ->route('pessoas.index')
                ->with('success', 'Pessoa excluída com sucesso!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('pessoas.index')
                    ->with('error', 'Não é possível excluir esta pessoa. È preciso apagar os dados associados.');
            }

            // Se for outro erro de banco
            return redirect()
                ->route('pessoas.index')
                ->with('error', 'Erro ao tentar excluir a pessoa.');
        }
    }
}
