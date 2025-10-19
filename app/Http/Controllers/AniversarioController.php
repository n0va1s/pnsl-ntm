<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class AniversarioController extends Controller
{
    public function index()
    {
        $nomeTeste = 'João da Silva';

        return view('emails.aniversario', [
            'nome' => $nomeTeste,
        ]);
    }
}
