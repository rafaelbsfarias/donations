<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gerencia a criação e validação de nonces no plugin
 */
class Asaas_Nonce_Manager {
    /**
     * Prefixo para todas as ações de nonce do plugin
     */
    const NONCE_PREFIX = 'asaas_easy_subscription_';
    
    /**
     * Ação para doação única
     */
    const ACTION_SINGLE_DONATION = 'single_donation';
    
    /**
     * Ação para doação recorrente
     */
    const ACTION_RECURRING_DONATION = 'recurring_donation';
    
    /**
     * Gera um campo de nonce para um formulário
     *
     * @param string $action Ação do formulário
     * @param bool $echo Se deve imprimir ou retornar
     * @return string|void HTML do campo nonce
     */
    public static function generate_nonce_field($action, $echo = true) {
        return wp_nonce_field(self::NONCE_PREFIX . $action, 'asaas_nonce', false, $echo);
    }
    
    /**
     * Verifica se um nonce é válido
     *
     * @param array $data Dados do formulário ou requisição
     * @param string $action Ação a ser verificada
     * @return bool Se o nonce é válido
     */
    public static function verify_nonce($data, $action) {
        if (!isset($data['asaas_nonce'])) {
            return false;
        }
        
        return wp_verify_nonce($data['asaas_nonce'], self::NONCE_PREFIX . $action);
    }
}