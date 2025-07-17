<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Aniversario;

class AniversarioController extends Controller
{
    public function index()
    {
        return view('emails.aniversario');
    }

    public function store(Request $request)
    {
        dd('Iniciando envio de e-mail de aniversário...');

        Mail::to([$pessoa->eml_pessoa, $pessoa->nom_pessoa])->send(new Aniversario(['fromName' => $request->input('fromName')]));
    }

}
