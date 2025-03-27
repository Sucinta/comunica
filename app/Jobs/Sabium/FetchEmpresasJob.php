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

class FetchEmpresasJob implements ShouldQueue
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
            Log::error('FetchEmpresasJob: Falha ao autenticar no ERP');
            return;
        }

        $url = config('services.sabium.url') . '/v3/executar_filtro';

        try {
            Log::info('Buscando empresas na API do ERP...');

            $response = Http::withHeaders([
                'pragma' => "dssession={$token}",
                'Accept' => 'application/json'
            ])->post($url, [
                'idfiltro' => 99100,
                'idcontexto' => 2,
                'parametros' => []
            ]);

            $empresas = json_decode($response->body(), true);

            if (!is_array($empresas['retorno'])) {
                Log::error('Erro: API não retornou um array válido.', ['response' => $response->body()]);
                return;
            }

            foreach ($empresas['retorno'] as $empresa) {

                dispatch(new ProcessEmpresasJob($empresa))->onQueue('sabium');

            }

            Log::info('Empresas enviadas para a fila com sucesso.');

        } catch (Exception $e) {
            Log::error('Erro ao buscar empresas: ' . $e->getMessage());
        }
    }
}
