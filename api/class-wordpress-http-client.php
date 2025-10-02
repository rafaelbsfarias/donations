<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'api/interfaces/interface-http-client.php';

/**
 * Implementação WordPress do cliente HTTP
 */
class Asaas_WordPress_HTTP_Client implements Asaas_HTTP_Client_Interface {
    /**
     * Realiza uma requisição GET usando wp_remote_get
     *
     * @param string $url URL da requisição
     * @param array $headers Cabeçalhos da requisição
     * @param array $options Opções adicionais
     * @return array|WP_Error Resposta da requisição
     */
    public function get($url, $headers = [], $options = []) {
        $args = [
            'headers' => $headers,
            'timeout' => isset($options['timeout']) ? $options['timeout'] : 30,
            'sslverify' => isset($options['sslverify']) ? $options['sslverify'] : true,
        ];
        
        return wp_remote_get($url, $args);
    }
    
    /**
     * Realiza uma requisição POST usando wp_remote_post
     *
     * @param string $url URL da requisição
     * @param array|string $body Corpo da requisição
     * @param array $headers Cabeçalhos da requisição
     * @param array $options Opções adicionais
     * @return array|WP_Error Resposta da requisição
     */
    public function post($url, $body = [], $headers = [], $options = []) {
        $args = [
            'body' => $body,
            'headers' => $headers,
            'timeout' => isset($options['timeout']) ? $options['timeout'] : 30,
            'sslverify' => isset($options['sslverify']) ? $options['sslverify'] : true,
        ];
        
        return wp_remote_post($url, $args);
    }
    
    /**
     * Verifica se a resposta contém erro
     * 
     * @param mixed $response Resposta da requisição
     * @return bool True se há erro, false caso contrário
     */
    public function has_error($response) {
        return is_wp_error($response);
    }
    
    /**
     * Obtém o corpo da resposta
     * 
     * @param mixed $response Resposta da requisição
     * @return string Corpo da resposta
     */
    public function get_body($response) {
        return wp_remote_retrieve_body($response);
    }
}