<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Aniversario;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;

$markdown = File::get(resource_path('views/emails/aniversario.md'));
$html = Str::markdown(
    Blade::render($markdown, ['nome' => 'João da Silva'])
);

class AniversarioController extends Controller
{
    public function index()
    {
        return view('emails.aniversario');
    }

}
