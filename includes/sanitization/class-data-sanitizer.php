<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe utilitária para sanitização de dados
 */
class Asaas_Data_Sanitizer {
    /**
     * Sanitiza texto simples
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_text($value) {
        return sanitize_text_field($value);
    }
    
    /**
     * Sanitiza endereço de email
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_email($value) {
        return sanitize_email($value);
    }
    
    /**
     * Remove caracteres não numéricos
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado contendo apenas números
     */
    public static function sanitize_numbers_only($value) {
        return preg_replace('/\D/', '', $value);
    }
    
    /**
     * Sanitiza CPF/CNPJ
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_cpf_cnpj($value) {
        return self::sanitize_numbers_only($value);
    }
    
    /**
     * Sanitiza número de cartão de crédito
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_card_number($value) {
        return self::sanitize_numbers_only($value);
    }
    
    /**
     * Sanitiza CEP/Código postal
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_postal_code($value) {
        return self::sanitize_numbers_only($value);
    }
    
    /**
     * Sanitiza número de telefone
     *
     * @param string $value Valor a ser sanitizado
     * @return string Valor sanitizado
     */
    public static function sanitize_phone($value) {
        return self::sanitize_numbers_only($value);
    }
    
    /**
     * Sanitiza um valor para float (suporta formato brasileiro)
     *
     * @param string|float $value Valor a ser convertido
     * @return float Valor convertido para float
     */
    public static function sanitize_float($value) {
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value); // Remove pontos de milhar
            $value = str_replace(',', '.', $value); // Substitui vírgula por ponto
        }
        return (float) $value;
    }
}