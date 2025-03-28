<?php

namespace App\Jobs\Sabium;

use App\Models\Sabium\Estado;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEstadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $estado;

    /**
     * Create a new job instance.
     */
    public function __construct(array $estado)
    {
        $this->estado = $estado;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            /*
             * ❌ Ignora se faltar uf ou idibge
             */
            if (empty($this->estado['uf']) || empty($this->estado['idibge'])) {
                Log::warning('ProcessEstadosJob: Estado inválido recebido.', ['estado' => $this->estado]);
                return;
            }

            Estado::updateOrCreate(
            /*
             * Chave única de referência
             */
                [
                    'uf' => $this->estado['uf'],
                    'codigo_ibge' => $this->estado['codigo_ibge']
                ],

                /*
                 * Campos atualização
                 */
                [
                    'nome' => $this->estado['nome'],
                    'timezone' => $this->estado['timezone']
                ]
            );

            Log::info('Estado salvo no banco: ' . $this->estado['nome']);

        } catch (Exception $e) {
            Log::error('Erro ao processar estado: ' . $e->getMessage());
        }
    }
}
