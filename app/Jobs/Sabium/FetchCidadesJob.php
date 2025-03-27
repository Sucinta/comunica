<?php

namespace App\Jobs\Sabium;

use App\Clients\Sabium\AuthClient;
use App\Models\Sabium\Estado;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchCidadesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $client = new AuthClient();
        $token = $client->authenticate();

        if (!$token) {
            Log::error('FetchCidadesJob: Falha ao autenticar no ERP');
            return;
        }

        // 🔥 Busca os estados cadastrados no banco
        $estados = Estado::all();

        foreach ($estados as $estado) {
            $url = config('services.sabium.url') . "/v3/executar_filtro";

            try {
                Log::info("Buscando cidades para o estado {$estado->uf}...");

                $response = Http::withHeaders([
                    'pragma' => "dssession={$token}",
                    'Accept' => 'application/json'
                ])->post($url, [
                    'idfiltro' => 99101,
                    'idcontexto' => 2,
                    'parametros' => [
                        [
                            'parametro' => 'uf',
                            'valorparametro' => $estado->uf
                        ]
                    ]
                ]);

                $cidades = json_decode($response->body(), true);

                if (!is_array($cidades['retorno'])) {
                    Log::error("Erro: API não retornou um array válido para {$estado->uf}.", ['response' => $response->body()]);
                    continue;
                }

                foreach ($cidades['retorno'] as $cidade) {

                    dispatch(new ProcessCidadesJob($cidade))->onQueue('sabium');

                }

                Log::info("Cidades do estado {$estado->uf} enviadas para a fila.");

            } catch (Exception $e) {
                Log::error("Erro ao buscar cidades para {$estado->uf}: " . $e->getMessage());
            }
        }
    }
}
