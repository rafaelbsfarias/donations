<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Garante que a classe Settings esteja disponível (caso ainda não tenha sido carregada).
if ( ! class_exists('Settings') ) {
    require_once __DIR__ . '/settings.php';
    $settings = new Settings();
} else {
    global $settings;
    if ( ! isset( $settings ) ) {
        $settings = new Settings();
    }
}

class Asaas_API {
    private $api_url;
    private $api_key;

    public function __construct() {
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/customers';
        $this->api_key = $settings->get('API_KEY');
    }

    /**
     * Registra um novo cliente na Asaas.
     *
     * @param array $customer_data Dados do cliente.
     * @return array|false Retorna a resposta decodificada ou false em caso de erro.
     */
    public function create_customer( $customer_data ) {
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $this->api_key,
                'accept'       => 'application/json',
                'User-Agent'   => 'teste'
            ),
            'body'    => wp_json_encode( $customer_data ),
            'timeout' => 60,
        );

        $response = wp_remote_post( $this->api_url, $args );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );
        return $result;
    }
}

function testar_asaas_create_customer() {
    $asaas_api = new Asaas_API();
    $customer_data = array(
        'name'    => 'João da Silva4',
        'email'   => 'joao.silva@example.com',
        'cpfCnpj' => '24971563792'
    );

    $result = $asaas_api->create_customer($customer_data);

    if ($result) {
        return '<h3>Resultado Final:</h3><pre>' . print_r($result, true) . '</pre>';
    } else {
        $response = wp_remote_post($asaas_api->api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $asaas_api->api_key,
                'accept'       => 'application/json',
                'User-Agent'   => 'teste'
            ),
            'body'    => wp_json_encode($customer_data),
            'timeout' => 60,
        ));
        $error_message = is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response);
        return '<p>Erro ao registrar cliente: ' . esc_html($error_message) . '</p>';
    }
}
add_shortcode( 'testar_asaas', 'testar_asaas_create_customer' );
