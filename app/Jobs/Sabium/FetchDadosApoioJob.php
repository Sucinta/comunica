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
use Illuminate\Support\Str;

class FetchDadosApoioJob implements ShouldQueue
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
            Log::error('[FetchDadosApoioJob] Falha ao autenticar no ERP');
            return;
        }

        $url = config('services.sabium.url') . '/v3/executar_filtro';

        try {
            Log::info('[FetchDadosApoioJob] Buscando dados na API do ERP...');

            $response = Http::withHeaders([
                'pragma' => "dssession={$token}",
                'Accept' => 'application/json'
            ])->post($url, [
                'idfiltro' => 99103,
                'idcontexto' => 2,
                'parametros' => []
            ]);

            $dados = json_decode($response->body(), true);

            if (!is_array($dados['retorno'])) {
                Log::error('[FetchDadosApoioJob] API não retornou um array válido.', ['response' => $response->body()]);
                return;
            }

            foreach ($dados['retorno'] as $item) {

                $tabela = $item['tabela'] ?? null;
                $dadosJson = $item['dados'] ?? null;

                if (!$tabela || !$dadosJson) {
                    Log::warning('[FetchDadosApoioJob] Item inválido em retorno do ERP.', ['item' => $item]);
                    continue;
                }

                $registro = json_decode($dadosJson, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("[FetchDadosApoioJob] Erro ao decodificar JSON da tabela '{$tabela}': " . json_last_error_msg());
                    continue;
                }

                /*
                 * Geração dinâmica do nome do Job
                 */
                $jobClass = $item['job'] ?? '\App\Jobs\Sabium\Process' . Str::studly(Str::singular($tabela)) . 'Job';

                if (!class_exists($jobClass)) {
                    Log::info("[FetchDadosApoioJob] Job não encontrado para: {$jobClass} ({$tabela})");
                    continue;
                }

                dispatch(new  $jobClass($registro))->onQueue('sabium');
            }

            Log::info('[FetchDadosApoioJob] Dados de Apoio enviadas para a fila com sucesso.');

        } catch (Exception $e) {
            Log::error('[FetchDadosApoioJob] Erro ao buscar dados de apoio: ' . $e->getMessage());
        }
    }
}
