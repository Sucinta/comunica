<?php

namespace App\Clients\Sabium;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthClient
{
    private string $authUrl;
    private string $baseUrl;
    private static ?string $sessionToken = null;
    private const CACHE_KEY = 'erp_session_token';
    private const CACHE_TTL = 7200; // Tempo de expiração do cache em segundos (2 horas)

    public function __construct()
    {
        $this->baseUrl = config('services.sabium.url');
        $this->authUrl = $this->baseUrl . '/v3/login';
    }

    /**
     * 🔐 Autentica e retorna o 'token' de sessão do ERP.
     * Se um 'token' válido existir no 'cache', ele será reutilizado.
     *
     * @throws ConnectionException
     */
    public function authenticate(): ?string
    {
        if (self::$sessionToken) {
            return self::$sessionToken;
        }

        // 🔎 Verifica se há um token válido no cache
        if (Cache::has(self::CACHE_KEY)) {
            $sessionToken = Cache::get(self::CACHE_KEY);

            if ($this->isTokenValid($sessionToken)) {
                self::$sessionToken = $sessionToken;
                return self::$sessionToken;
            }

            // ❌ Token inválido, precisa ser renovado
            Cache::forget(self::CACHE_KEY);
        }

        // 🔄 Caso não tenha um 'token' válido, realiza uma nova autenticação
        return $this->requestNewSession();
    }

    /**
     * ✅ Testa se o token ainda é válido antes de usá-lo.
     *
     * @throws ConnectionException
     */
    private function isTokenValid(string $sessionToken): bool
    {
        $url = $this->baseUrl . '/v3/login_complementos'; // Testa a API sem impactar dados

        $response = Http::withHeaders([
            'pragma' => "dssession={$sessionToken}",
            'Accept' => 'application/json'
        ])->get($url);

        // ❌ Se a API retorna SessionExpired, o token não é mais válido
        if ($response->status() === 401 || str_contains($response->body(), 'SessionExpired')) {
            Log::warning('Token expirado detectado, descartando...');
            return false;
        }

        return true;
    }

    /**
     * 🔄 Faz a requisição de uma nova sessão na API do ERP.
     */
    private function requestNewSession(): ?string
    {
        try {
            Log::info('Autenticando na API do ERP...');

            $response = Http::get("{$this->authUrl}/" . config('services.sabium.usuario') . "/" . config('services.sabium.senha'));

            if ($response->successful() && $response->status() === 204) {
                $headers = $response->header('pragma');

                if (!empty($headers) && preg_match('/dssession=([^,]+)/', $headers, $matches)) {
                    self::$sessionToken = $matches[1];

                    // 💾 Salva o token no cache
                    $this->saveSession(self::$sessionToken);
                    $this->loginComplementos(self::$sessionToken);

                    return self::$sessionToken;
                }
            }

            Log::error('Falha na autenticação do ERP');
            return null;

        } catch (Exception $e) {
            Log::error('Erro ao conectar com o ERP: ' . $e->getCode());
            return null;
        }
    }

    /**
     * 💾 Salva a sessão no 'Cache' para reutilização.
     */
    private function saveSession(string $sessionToken): void
    {
        Cache::put(self::CACHE_KEY, $sessionToken, self::CACHE_TTL);
    }

    /**
     * 📦 Faz a requisição dos complementos do 'login'.
     */
    private function loginComplementos(string $sessionToken): void
    {
        $url =  $this->baseUrl . '/v3/login_complementos?ambiente=vendedor';

        try {
            Log::info("Buscando complementos do login...");

            $response = Http::withHeaders([
                'pragma' => "dssession={$sessionToken}",
                'Accept' => 'application/json'
            ])->get($url);

            $complementos = json_decode($response->body(), true)['retorno'] ?? [];

            Log::info('Login complementos carregados.');

        } catch (Exception $e) {
            Log::error('Erro ao buscar complementos do login: ' . $e->getMessage());
        }
    }
}
