<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pessoa;
use App\Mail\Aniversario;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarEmailAniversario extends Command
{

    protected $signature = 'aniversario:enviar';
    protected $description = 'Envia e-mails de aniversário para as pessoas que fazem aniversário hoje';

    public function handle()
    {
        $hoje = Carbon::now()->format('d/m');
        $pessoas = Pessoa::whereRaw("strftime('%d/%m',dat_nascimento) = ?", [$hoje])->get();

        if ($pessoas->isEmpty()) {
            $this->info("Nenhuma pessoa com aniversário hoje encontrada: ". $hoje);
            return;
        }

        foreach ($pessoas as $pessoa) {
            if (empty($pessoa->eml_pessoa)) {
                $this->warn("E-mail não encontrado para a pessoa {$pessoa->nom_pessoa}");
                continue;
            }
            // dd($pessoa->eml_pessoa, $pessoa->nom_pessoa);
            $this->info("Tentando enviar e-mail para {$pessoa->nom_pessoa} ({$pessoa->eml_pessoa})");
            Mail::to($pessoa->eml_pessoa)->send(new Aniversario(['fromName' => $pessoa->nom_pessoa]));
            $this->info("E-mail de aniversário enviado para {$pessoa->nom_pessoa} ({$pessoa->eml_pessoa})");


        }
    }
}
