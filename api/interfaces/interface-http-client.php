<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface para clientes HTTP
 */
interface Asaas_HTTP_Client_Interface {
    /**
     * Realiza uma requisição GET
     *
     * @param string $url URL da requisição
     * @param array $headers Cabeçalhos da requisição
     * @param array $options Opções adicionais
     * @return mixed Resposta da requisição
     */
    public function get($url, $headers = [], $options = []);
    
    /**
     * Realiza uma requisição POST
     *
     * @param string $url URL da requisição
     * @param array|string $body Corpo da requisição
     * @param array $headers Cabeçalhos da requisição
     * @param array $options Opções adicionais
     * @return mixed Resposta da requisição
     */
    public function post($url, $body = [], $headers = [], $options = []);
    
    /**
     * Verifica se a resposta contém erro
     * 
     * @param mixed $response Resposta da requisição
     * @return bool True se há erro, false caso contrário
     */
    public function has_error($response);
    
    /**
     * Obtém o corpo da resposta
     * 
     * @param mixed $response Resposta da requisição
     * @return string Corpo da resposta
     */
    public function get_body($response);
}