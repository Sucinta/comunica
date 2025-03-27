<?php

namespace App\Jobs\Sabium;

use App\Clients\Sabium\AuthClient;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchEstadosJob implements ShouldQueue
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
                Log::error('FetchEstadosJob: Falha ao autenticar no ERP');
                return;
            }

            $url = config('services.sabium.url') . '/v3/executar_filtro';

            try {
                Log::info('Buscando estados na API do ERP...');

                $response = Http::withHeaders([
                    'pragma' => "dssession={$token}",
                    'Accept' => 'application/json'
                ])->post($url, [
                    'idfiltro' => 99100,
                    'idcontexto' => 2,
                    'parametros' => []
                ]);

                $estados = json_decode($response->body(), true);

                if (!is_array($estados['retorno'])) {
                    Log::error('Erro: API não retornou um array válido.', ['response' => $response->body()]);
                    return;
                }

                foreach ($estados['retorno'] as $estado) {

                    dispatch(new ProcessEstadosJob($estado))->onQueue('sabium');

                }

                Log::info('Estados enviados para a fila com sucesso.');

            } catch (Exception $e) {
                Log::error('Erro ao buscar estados: ' . $e->getMessage());
            }
        }
}
