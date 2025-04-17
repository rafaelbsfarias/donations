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
        
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/customers';
        
        // Verifica a chave de API
        $encrypted_key_option = get_option('donationsaas_api_key_encrypted');
        
        if ($encrypted_key_option && defined('SECRET_ENCRYPTION_KEY')) {
            try {
                $this->api_key = EncryptionHelper::decrypt($encrypted_key_option);
            } catch (Exception $e) {
                $this->api_key = '';
            }
        } else {
            // Tente obter a chave diretamente das configurações (não recomendado, apenas para debug)
            $this->api_key = $settings->get('API_KEY', '');
        }
    }

    public function create_customer($customer_data) {
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
        
        // Realiza a requisição
        $response = wp_remote_post($this->api_url, $args);
        
        // Debug da resposta bruta
        if (is_wp_error($response)) {
            return array('errors' => array('message' => $response->get_error_message()));
        } else {
            $body = wp_remote_retrieve_body($response);
            $decoded = json_decode($body, true);
            return $decoded;
        }
    }
}