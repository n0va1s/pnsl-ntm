<?php

namespace App\Console\Commands;

use App\Mail\AniversarioMail;
use App\Models\Pessoa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarEmailAniversario extends Command
{
    protected $signature = 'mov:enviar-emails-aniversario';

    protected $description = 'Identifica aniversariantes do dia e dispara e-mails de felicidades';

    public function handle(): int
    {
        $hoje = now();
        $this->info("Iniciando processamento de aniversariantes para: {$hoje->format('d/m')}");

        Log::info('mov:enviar-emails-aniversario iniciado', [
            'data' => $hoje->format('Y-m-d'),
        ]);

        try {
            $query = Pessoa::whereMonth('dat_nascimento', $hoje->month)
                ->whereDay('dat_nascimento', $hoje->day)
                ->whereNotNull('eml_pessoa')
                ->where('eml_pessoa', '<>', '');

            $total = $query->count();

            if ($total === 0) {
                $this->info('Nenhum aniversariante encontrado para hoje.');
                Log::info('mov:enviar-emails-aniversario — nenhum aniversariante encontrado.');

                return self::SUCCESS;
            }

            $this->info("Encontrado(s): {$total} aniversariante(s).");

            $enviados  = 0;
            $ignorados = 0;
            $falhas    = 0;

            $query->chunk(100, function ($pessoas) use (&$enviados, &$ignorados, &$falhas) {
                foreach ($pessoas as $pessoa) {
                    if (! filter_var($pessoa->eml_pessoa, FILTER_VALIDATE_EMAIL)) {
                        $this->warn("E-mail inválido ignorado: {$pessoa->eml_pessoa} (ID: {$pessoa->idt_pessoa})");
                        Log::warning('mov:enviar-emails-aniversario — e-mail inválido ignorado', [
                            'pessoa_id' => $pessoa->idt_pessoa,
                            'email'     => $pessoa->eml_pessoa,
                        ]);
                        $ignorados++;

                        continue;
                    }

                    try {
                        Mail::to($pessoa->eml_pessoa)->send(
                            new AniversarioMail(['nome' => $pessoa->nom_pessoa])
                        );

                        $this->line("<info>Enviado:</info> {$pessoa->nom_pessoa} ({$pessoa->eml_pessoa})");
                        $enviados++;

                    } catch (\Throwable $e) {
                        $this->error("Falha ao enviar para {$pessoa->eml_pessoa}: {$e->getMessage()}");

                        Log::error('mov:enviar-emails-aniversario — falha no envio', [
                            'pessoa_id' => $pessoa->idt_pessoa,
                            'email'     => $pessoa->eml_pessoa,
                            'exception' => get_class($e),
                            'message'   => $e->getMessage(),
                        ]);

                        $falhas++;
                    }
                }
            });

            $this->info("Concluído — enviados: {$enviados}, ignorados: {$ignorados}, falhas: {$falhas}.");

            Log::info('mov:enviar-emails-aniversario concluído', [
                'enviados'  => $enviados,
                'ignorados' => $ignorados,
                'falhas'    => $falhas,
            ]);

            // Retorna FAILURE se houve falhas para o scheduler registrar
            return $falhas > 0 ? self::FAILURE : self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Erro inesperado: {$e->getMessage()}");

            Log::error('mov:enviar-emails-aniversario — exceção não tratada', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return self::FAILURE;
        }
    }
}
