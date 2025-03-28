<?php

namespace App\Jobs\Sabium;

use App\Models\Sabium\Cidade;
use App\Models\Sabium\Estado;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCidadesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $cidade;

    /**
     * Create a new job instance.
     */
    public function __construct(array $cidade)
    {
        $this->cidade = $cidade;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            /*
             * Validação mínima antes de cadastrar
             */
            if (empty($this->cidade['idcidade']) || empty($this->cidade['uf'])) {
                Log::warning('ProcessCidadesJob: Cidade ignorada por falta de dados obrigatórios.', ['dados' => $this->cidade]);
                return;
            }

            /*
             * Encontrar o estado com base na UF
             */
            $estado = Estado::where('uf', $this->cidade['uf'])->first();

            if (!$estado) {
                Log::warning('UF não encontrada para cidade', ['uf' => $this->cidade['uf'], 'cidade' => $this->cidade['cidade']]);
                return;
            }

            /*
             * Atualiza ou cria a cidade com base no sabium_id
             */
            Cidade::updateOrCreate(
            /*
             * Chave única de referência
             */
                ['codigo_ibge' => $this->cidade['codigo_ibge']],

                /*
                 * Campos atualização
                 */
                [
                    'estado_id' => $estado->id,
                    'sabium_id' => $this->cidade['sabium_id'],
                    'nome' => $this->cidade['nome'],
                    'timezone' => $this->cidade['timezone'] ?? $estado->timezone, // herda se não vier
                ]
            );

            Log::info("Cidade salva no banco: {$this->cidade['cidade']} ({$estado->uf})");

        } catch (Exception $e) {
            Log::error('Erro ao processar cidade.', [
                'erro' => $e->getMessage(),
                'dados' => $this->cidade
            ]);
        }
    }
}
