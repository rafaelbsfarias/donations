<?php
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../EncryptionHelper.php';

class PagamentoUnicoService {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        // Usar settings e não constantes
        $settings = new Settings();
        $base_url = $settings->get('BASE_URL');
        $this->apiUrl = rtrim($base_url, '/');
        
        // Buscar a chave criptografada da mesma forma que outras classes
        $encryptedKey = get_option('donationsaas_api_key_encrypted');
        if (!empty($encryptedKey) && defined('SECRET_ENCRYPTION_KEY')) {
            try {
                $this->apiKey = EncryptionHelper::decrypt($encryptedKey);
            } catch (Exception $e) {
                error_log("PagamentoUnicoService: Erro ao descriptografar chave API: " . $e->getMessage());
                // Fallback para a chave não criptografada
                $this->apiKey = $settings->get('API_KEY', '');
            }
        } else {
            $this->apiKey = $settings->get('API_KEY', '');
        }
    }

    public function criarCliente(array $clienteData) {
        $url = $this->apiUrl . '/customers';

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'access_token'  => $this->apiKey,
                'accept'        => 'application/json',
            ],
            'body' => json_encode($clienteData),
            'timeout' => 60,
        ]);

        return $this->tratarResposta($response);
    }

    public function criarCobranca(array $cobrancaData) {
        $url = $this->apiUrl . '/payments';

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'access_token'  => $this->apiKey,
                'accept'        => 'application/json',
            ],
            'body' => json_encode($cobrancaData),
            'timeout' => 60,
        ]);

        return $this->tratarResposta($response);
    }

    private function tratarResposta($response) {
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'data' => $body];
        }

        return ['success' => false, 'error' => $body];
    }
}
