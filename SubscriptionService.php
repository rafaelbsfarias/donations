<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Settings')) {
    require_once __DIR__ . '/settings.php';
    $settings = new Settings();
} else {
    global $settings;
    if (!isset($settings)) {
        $settings = new Settings();
    }
}

require_once __DIR__ . '/EncryptionHelper.php';

class SubscriptionService {
    private $api_url;
    private $api_key;

    public function __construct() {
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/subscriptions';
        
        // Verificar em ambas as opções possíveis
        $encryptedKey = get_option('donationsaas_api_key_encrypted');
        if (empty($encryptedKey)) {
            $encryptedKey = get_option('donations_api_key_encrypted');
        }
        
        if ($encryptedKey && defined('SECRET_ENCRYPTION_KEY')) {
            try {
                $this->api_key = EncryptionHelper::decrypt($encryptedKey);
                error_log("SubscriptionService: API key descriptografada com sucesso");
            } catch (Exception $e) {
                error_log("SubscriptionService: Erro ao descriptografar API key: " . $e->getMessage());
                $this->api_key = '';
            }
        } else {
            // Backup: tentar obter a chave diretamente das configurações
            $this->api_key = $settings->get('API_KEY', '');
            error_log("SubscriptionService: Usando API key das configurações. Definida? " . (!empty($this->api_key) ? 'Sim' : 'Não'));
        }
    }

    public function create_subscription($subscriptionData) {
        // Adicionar debug
        error_log("SubscriptionService: Tentando criar assinatura com dados: " . wp_json_encode($subscriptionData));
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $this->api_key,
                'accept'       => 'application/json'
            ),
            'body'    => wp_json_encode($subscriptionData),
            'timeout' => 60,
        );
        
        // Debug dos parâmetros de requisição (sem a chave API para segurança)
        $debug_args = $args;
        if (isset($debug_args['headers']['access_token'])) {
            $debug_args['headers']['access_token'] = substr($debug_args['headers']['access_token'], 0, 5) . '...';
        }
        error_log("SubscriptionService: Parâmetros da requisição = " . wp_json_encode($debug_args));

        $response = wp_remote_post($this->api_url, $args);
        
        if (is_wp_error($response)) {
            error_log("SubscriptionService: Erro na requisição = " . $response->get_error_message());
            return array('errors' => array('message' => $response->get_error_message()));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        error_log("SubscriptionService: Código de status HTTP = " . $status_code);
        
        $body = wp_remote_retrieve_body($response);
        error_log("SubscriptionService: Corpo da resposta = " . $body);
        
        return json_decode($body, true);
    }
}
