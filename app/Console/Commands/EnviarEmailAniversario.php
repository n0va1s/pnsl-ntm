<?php

namespace App\Console\Commands;

use App\Mail\AniversarioMail;
use App\Models\Pessoa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarEmailAniversario extends Command
{
    protected $signature = 'aniversario:enviar';

    protected $description = 'Envia e-mails para aniversariantes do dia';

    public function handle()
    {
        $hoje = Carbon::now();
        $dia = $hoje->day;
        $mes = $hoje->month;

        $pessoas = Pessoa::whereMonth('dat_nascimento', $mes)
            ->whereDay('dat_nascimento', $dia)
            ->get();

        if ($pessoas->isEmpty()) {
            $this->info('Nenhuma pessoa com aniversário hoje encontrada: '.$hoje->format('d/m/Y'));

            return;
        }

        foreach ($pessoas as $pessoa) {
            if (empty($pessoa->eml_pessoa)) {
                $this->warn("E-mail não encontrado para {$pessoa->eml_pessoa}");

                continue;
            }

            $this->info("Tentando enviar e-mail para ({$pessoa->eml_pessoa})");

            try {
                Mail::to($pessoa->eml_pessoa)->send(
                    new AniversarioMail([
                        'nome' => $pessoa->nom_pessoa,
                    ])
                );
                $this->info("E-mail de aniversário enviado com sucesso para {$pessoa->eml_pessoa}");
            } catch (\Exception $e) {
                $this->error("Falha ao enviar e-mail para {$pessoa->eml_pessoa}. Erro: ".$e->getMessage());
            }
        }

        $this->info('Processo de envio de e-mails de aniversário concluído.');
    }
}
