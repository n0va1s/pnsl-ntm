<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use App\Models\Evento;
use App\Models\Ficha;
use App\Models\FichaVem;
use App\Models\TipoMovimento;
use App\Services\FichaService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $movimentos = TipoMovimento::select(
            'idt_movimento',
            'nom_movimento',
            'des_sigla'
        )->get();

        $proximoseventos = Evento::with(['movimento'])
            ->where('dat_inicio', '>=', now())
            ->orderBy('dat_inicio', 'asc')
            ->take(5)
            ->select('idt_evento', 'des_evento', 'dat_inicio', 'idt_movimento')
            ->get();

        return view('welcome', compact('proximoseventos', 'movimentos'));
    }

    public function contato(Request $request)
    {
        $data = $request->validate([
            'nom_contato' => 'required|string|max:255',
            'eml_contato' => 'nullable|email|max:255',
            'tel_contato' => 'required|string|max:20',
            'txt_mensagem' => 'required|string|max:1000',
            'idt_movimento' => 'required|exists:tipo_movimento,idt_movimento',
        ]);

        Contato::create($data);
        return redirect()->route('home')->with('message', 'Recebemos seu contato. Em breve retornaremos!');
    }

    public function fichaVem()
    {
        $ficha = new Ficha();
        $ficha->idt_movimento = TipoMovimento::VEM;

        return view('ficha.formVEM', array_merge(FichaService::dadosFixosFicha($ficha), [
            'ficha' => $ficha,
            'eventos' => Evento::where('idt_movimento', TipoMovimento::VEM)->get(),
            'movimentopadrao' => TipoMovimento::VEM,
        ]));

        return view('ficha.formVEM', compact('ficha'));
    }

    public function fichaEcc()
    {
        return view('ficha.formECC');
    }

    public function fichaSgm()
    {
        return view('ficha.formSGM');
    }
}
