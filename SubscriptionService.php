<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists('Settings') ) {
    require_once __DIR__ . '/settings.php';
    $settings = new Settings();
} else {
    global $settings;
    if ( ! isset($settings) ) {
        $settings = new Settings();
    }
}

class SubscriptionService {
    private $api_url;
    private $api_key;

    public function __construct() {
        global $settings;
        // BASE_URL do .env é a raiz da API, aqui concatenamos com o endpoint "subscriptions"
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/subscriptions';
        $this->api_key = $settings->get('API_KEY');
    }

    // Adicione estes métodos na classe SubscriptionService
    public function getApiUrl() {
        return $this->api_url;
    }
    
    public function getApiKey() {
        return $this->api_key;
    }

    /**
     * Cria uma nova assinatura na API do Asaas.
     *
     * @param array $subscriptionData Dados da assinatura conforme a documentação.
     * @return array|false Retorna a resposta decodificada ou false em caso de erro.
     */
    public function create_subscription( $subscriptionData ) {
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $this->api_key,
                'accept'       => 'application/json',
                'User-Agent'   => 'teste'
            ),
            'body'    => wp_json_encode( $subscriptionData ),
            'timeout' => 60,
        );

        $response = wp_remote_post( $this->api_url, $args );
        if ( is_wp_error( $response ) ) {
            return false;
        }
        $body = wp_remote_retrieve_body( $response );
        return json_decode( $body, true );
    }
}
