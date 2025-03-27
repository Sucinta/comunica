<?php

namespace App\Jobs\Sabium;

use App\Models\Sabium\Empresa;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmpresasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $empresa;

    /**
     * Create a new job instance.
     */
    public function __construct(array $empresa)
    {
        $this->empresa = $empresa;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            Empresa::updateOrCreate(
            /*
             * Chave única de referência
             */
                [
                    'id' => $this->empresa['id']
                ],

                /*
                 * Campos atualização
                 */
                [
                    'razao_social' => $this->empresa['razao_social'],
                    'fantasia' => $this->empresa['fantasia'],
                    'nome_sistema' => $this->empresa['nome_sistema'] ?? null
                ]
            );

            Log::info('Empresa salva no banco: ' . $this->empresa['razao_social']);

        } catch (Exception $e) {
            Log::error('Erro ao processar empresa: ' . $e->getMessage());
        }
    }
}
