<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/EncryptionHelper.php';

// Recupera o objeto de configurações
$settings = new Settings();

class Asaas_API {
    private $api_url;
    private $api_key;

    public function __construct() {
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/customers';
        
        // Debug das configurações
        error_log("Asaas_API: BASE_URL = " . $base_url);
        error_log("Asaas_API: API endpoint = " . $this->api_url);
        
        // Verifica a chave de API
        $encrypted_key_option = get_option('donationsaas_api_key_encrypted');
        error_log("Asaas_API: API key criptografada encontrada? " . ($encrypted_key_option ? 'Sim' : 'Não'));
        
        if ($encrypted_key_option && defined('SECRET_ENCRYPTION_KEY')) {
            try {
                $this->api_key = EncryptionHelper::decrypt($encrypted_key_option);
                error_log("Asaas_API: API key descriptografada com sucesso. Primeiros 5 caracteres: " . substr($this->api_key, 0, 5) . "...");
            } catch (Exception $e) {
                error_log("Asaas_API: Erro ao descriptografar API key: " . $e->getMessage());
                $this->api_key = '';
            }
        } else {
            // Tente obter a chave diretamente das configurações (não recomendado, apenas para debug)
            $this->api_key = $settings->get('API_KEY', '');
            error_log("Asaas_API: Usando API key das configurações (não criptografada). Definida? " . (!empty($this->api_key) ? 'Sim' : 'Não'));
        }
        
        // Verifica se a chave API está disponível
        if (empty($this->api_key)) {
            error_log("Asaas_API: ERRO CRÍTICO - API key não disponível!");
        }
    }

    public function create_customer($customer_data) {
        // Debug dos dados do cliente
        error_log("Asaas_API create_customer: Dados do cliente = " . wp_json_encode($customer_data));
        
        // Prepara os parâmetros da requisição
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $this->api_key,
                'accept'       => 'application/json',
                'User-Agent'   => 'WordPress/DonationSaaS'
            ),
            'body'    => wp_json_encode($customer_data),
            'timeout' => 60,
        );
        
        // Debug dos parâmetros de requisição (sem a chave API para segurança)
        $debug_args = $args;
        $debug_args['headers']['access_token'] = substr($debug_args['headers']['access_token'], 0, 5) . '...';
        error_log("Asaas_API create_customer: Parâmetros da requisição = " . wp_json_encode($debug_args));
        
        // Realiza a requisição
        $response = wp_remote_post($this->api_url, $args);
        
        // Debug da resposta bruta
        if (is_wp_error($response)) {
            error_log("Asaas_API create_customer: Erro na requisição = " . $response->get_error_message());
            return array('errors' => array('message' => $response->get_error_message()));
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            error_log("Asaas_API create_customer: Código de status HTTP = " . $status_code);
            
            $body = wp_remote_retrieve_body($response);
            error_log("Asaas_API create_customer: Corpo da resposta = " . $body);
            
            $decoded = json_decode($body, true);
            return $decoded;
        }
    }
}