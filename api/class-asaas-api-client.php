<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'api/interfaces/interface-http-client.php';

/**
 * Cliente para API do Asaas
 */
class Asaas_API_Client {
    /**
     * URL base da API
     *
     * @var string
     */
    private $api_base_url;
    
    /**
     * Token de acesso à API
     *
     * @var string
     */
    private $access_token;
    
    /**
     * Cliente HTTP
     *
     * @var Asaas_HTTP_Client_Interface
     */
    private $http_client;
    
    /**
     * Construtor
     *
     * @param string $api_base_url URL base da API
     * @param string $access_token Token de acesso à API
     * @param Asaas_HTTP_Client_Interface $http_client Cliente HTTP
     */
    public function __construct($api_base_url, $access_token, Asaas_HTTP_Client_Interface $http_client) {
        $this->api_base_url = $api_base_url;
        $this->access_token = $access_token;
        $this->http_client = $http_client;
    }
    
    /**
     * Realiza requisição GET para a API
     *
     * @param string $endpoint Endpoint da API
     * @param array $params Parâmetros da requisição
     * @return array Resposta da API em formato associativo
     */
    public function get($endpoint, $params = []) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        $headers = [
            'Accept' => 'application/json',
            'access_token' => $this->access_token,
        ];
        
        $response = $this->http_client->get($url, $headers, ['sslverify' => true]);
        
        if ($this->http_client->has_error($response)) {
            return $response;
        }
        
        $body = $this->http_client->get_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Realiza requisição POST para a API
     *
     * @param string $endpoint Endpoint da API
     * @param array $data Dados a serem enviados no corpo da requisição
     * @return array Resposta da API em formato associativo
     */
    public function post($endpoint, $data = []) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'access_token' => $this->access_token
        ];
        
        // Converter array para JSON
        $body = json_encode($data);
        
        $response = $this->http_client->post($url, $body, $headers, ['sslverify' => true]);
        
        if ($this->http_client->has_error($response)) {
            return $response;
        }
        
        $body = $this->http_client->get_body($response);
        return json_decode($body, true);
    }
}