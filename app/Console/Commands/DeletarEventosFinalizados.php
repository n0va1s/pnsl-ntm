<?php

namespace App\Console\Commands;

use App\Models\Evento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeletarEventosFinalizados extends Command
{
    protected $signature = 'mov:deletar-eventos-finalizados';

    protected $description = 'Marca como deletados (soft delete) os eventos cujo dat_termino já passou';

    public function handle(): int
    {
        $this->info('Iniciando encerramento de eventos finalizados...');

        try {
            // Busca eventos finalizados que ainda não foram soft-deletados
            $eventosExpirados = Evento::where('dat_termino', '<=', now())
                ->whereNull('deleted_at')
                ->get();

            if ($eventosExpirados->isEmpty()) {
                $this->info('Nenhum evento finalizado encontrado.');
                Log::info('mov:deletar-eventos-finalizados — nenhum evento para encerrar.');

                return self::SUCCESS;
            }

            $count = 0;

            foreach ($eventosExpirados as $evento) {
                // Usa o método delete() do SoftDeletes, mas sobrescreve o valor
                // de deleted_at para usar dat_termino em vez de now()
                $evento->deleted_at = $evento->dat_termino;
                $evento->saveQuietly();

                $this->line("Encerrado: [{$evento->idt_evento}] {$evento->des_evento} (término: {$evento->dat_termino})");
                $count++;
            }

            $this->info("{$count} evento(s) encerrado(s) com sucesso.");

            Log::info('mov:deletar-eventos-finalizados concluído', [
                'total_encerrados' => $count,
            ]);

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Erro ao encerrar eventos: {$e->getMessage()}");

            Log::error('mov:deletar-eventos-finalizados — exceção não tratada', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return self::FAILURE;
        }
    }
}
