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
    private static $instance_count = 0;
    private $instance_id;

    public function __construct() {
        self::$instance_count++;
        $this->instance_id = self::$instance_count;
        
        error_log("[DEBUG] Asaas_API::__construct - Nova instância #{$this->instance_id} criada às " . date('Y-m-d H:i:s'));
        
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/customers';
        
        // Debug das configurações
        error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - BASE_URL = " . $base_url);
        error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - API endpoint = " . $this->api_url);
        
        // Verifica a chave de API
        $encrypted_key_option = get_option('donationsaas_api_key_encrypted');
        error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - API key criptografada encontrada? " . ($encrypted_key_option ? 'Sim' : 'Não'));
        
        if ($encrypted_key_option && defined('SECRET_ENCRYPTION_KEY')) {
            try {
                $this->api_key = EncryptionHelper::decrypt($encrypted_key_option);
                error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - API key descriptografada com sucesso");
            } catch (Exception $e) {
                error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - Erro ao descriptografar API key: " . $e->getMessage());
                $this->api_key = '';
            }
        } else {
            // Tente obter a chave diretamente das configurações (não recomendado, apenas para debug)
            $this->api_key = $settings->get('API_KEY', '');
            error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - Usando API key das configurações");
        }
        
        // Verifica se a chave API está disponível
        if (empty($this->api_key)) {
            error_log("[DEBUG] Asaas_API::__construct #{$this->instance_id} - ERRO CRÍTICO - API key não disponível!");
        }
    }

    public function create_customer($customer_data) {
        $request_id = uniqid();
        
        // Debug dos dados do cliente
        error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Chamada iniciada às " . date('Y-m-d H:i:s'));
        error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Dados do cliente = " . wp_json_encode($customer_data));
        
        // Obter backtrace para ver de onde a função está sendo chamada
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($backtrace as $index => $call) {
            error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Backtrace #{$index}: " 
                . (isset($call['class']) ? $call['class'] . '::' : '') 
                . $call['function'] . ' - File: ' 
                . (isset($call['file']) ? $call['file'] : 'unknown') . ' Line: ' 
                . (isset($call['line']) ? $call['line'] : 'unknown'));
        }
        
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
        error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Parâmetros da requisição = " . wp_json_encode($debug_args));
        
        // Realiza a requisição
        $response = wp_remote_post($this->api_url, $args);
        
        // Debug da resposta bruta
        if (is_wp_error($response)) {
            error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Erro na requisição = " . $response->get_error_message());
            return array('errors' => array('message' => $response->get_error_message()));
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Código de status HTTP = " . $status_code);
            
            $body = wp_remote_retrieve_body($response);
            error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Corpo da resposta = " . $body);
            
            $decoded = json_decode($body, true);
            
            error_log("[DEBUG] Asaas_API::create_customer #{$this->instance_id}-{$request_id} - Requisição concluída às " . date('Y-m-d H:i:s'));
            
            return $decoded;
        }
    }
}