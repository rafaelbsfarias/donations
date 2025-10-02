<?php

if (!defined('ABSPATH')) {
    exit;
}

class Asaas_Admin_Settings {
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('asaas_plugin_settings', 'asaas_api_key', [$this, 'validate_api_key']);
        register_setting('asaas_plugin_settings', 'asaas_environment');
        register_setting('asaas_plugin_settings', 'asaas_recaptcha_site_key', [$this, 'validate_recaptcha_key']);
        register_setting('asaas_plugin_settings', 'asaas_recaptcha_secret_key', [$this, 'validate_recaptcha_key']);
        register_setting('asaas_plugin_settings', 'asaas_enable_donation_logs');

        add_settings_section(
            'asaas_general_settings',
            __('General Settings', 'asaas-easy-subscription-plugin'),
            null,
            'asaas-settings'
        );

        add_settings_field(
            'asaas_api_key',
            __('API Key', 'asaas-easy-subscription-plugin'),
            [$this, 'render_api_key_field'],
            'asaas-settings',
            'asaas_general_settings'
        );

        add_settings_field(
            'asaas_environment',
            __('Environment', 'asaas-easy-subscription-plugin'),
            [$this, 'render_environment_field'],
            'asaas-settings',
            'asaas_general_settings'
        );

        add_settings_field(
            'asaas_recaptcha_site_key',
            __('reCAPTCHA Site Key', 'asaas-easy-subscription-plugin'),
            [$this, 'render_recaptcha_site_key_field'],
            'asaas-settings',
            'asaas_general_settings'
        );

        add_settings_field(
            'asaas_recaptcha_secret_key',
            __('reCAPTCHA Secret Key', 'asaas-easy-subscription-plugin'),
            [$this, 'render_recaptcha_secret_key_field'],
            'asaas-settings',
            'asaas_general_settings'
        );

        add_settings_field(
            'asaas_enable_donation_logs',
            __('Habilitar logs de doações', 'asaas-easy-subscription-plugin'),
            [$this, 'render_enable_logs_field'],
            'asaas-settings',
            'asaas_general_settings'
        );
    }

    public function render_api_key_field() {
        $value = get_option('asaas_api_key', '');
        echo '<input type="text" name="asaas_api_key" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_environment_field() {
        $value = get_option('asaas_environment', 'sandbox');
        ?>
        <select name="asaas_environment">
            <option value="sandbox" <?php selected($value, 'sandbox'); ?>><?php esc_html_e('Sandbox', 'asaas-easy-subscription-plugin'); ?></option>
            <option value="production" <?php selected($value, 'production'); ?>><?php esc_html_e('Production', 'asaas-easy-subscription-plugin'); ?></option>
        </select>
        <?php
    }

    public function render_recaptcha_site_key_field() {
        $value = get_option('asaas_recaptcha_site_key', '');
        echo '<input type="text" name="asaas_recaptcha_site_key" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_recaptcha_secret_key_field() {
        $value = get_option('asaas_recaptcha_secret_key', '');
        echo '<input type="text" name="asaas_recaptcha_secret_key" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_enable_logs_field() {
        $value = get_option('asaas_enable_donation_logs', false);
        ?>
        <input type="checkbox" name="asaas_enable_donation_logs" value="1" <?php checked($value, 1); ?>>
        <p class="description">Registra o nome do doador e o valor doado para fins de depuração.</p>
        <?php
    }

    /**
     * Get the base URL for the Asaas API based on the selected environment.
     *
     * @return string The base URL for the API.
     */
    public static function get_api_base_url() {
        $environment = get_option('asaas_environment', 'sandbox');
        if ($environment === 'production') {
            return 'https://api.asaas.com/v3/';
        }
        return 'https://api-sandbox.asaas.com/v3/';
    }

    /**
     * Valida a chave da API Asaas
     */
    public function validate_api_key($value) {
        if (empty($value)) {
            add_settings_error('asaas_api_key', 'api_key_empty', __('A chave da API Asaas é obrigatória.', 'asaas-easy-subscription-plugin'));
            return '';
        }
        
        // Validação básica de formato (chaves Asaas têm padrão específico)
        if (!preg_match('/^[a-zA-Z0-9]{32,}$/', $value)) {
            add_settings_error('asaas_api_key', 'api_key_invalid', __('A chave da API Asaas parece inválida. Verifique o formato.', 'asaas-easy-subscription-plugin'));
        }
        
        return sanitize_text_field($value);
    }

    /**
     * Valida chaves do reCAPTCHA
     */
    public function validate_recaptcha_key($value) {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]{20,}$/', $value)) {
            add_settings_error('asaas_recaptcha_keys', 'recaptcha_invalid', __('A chave do reCAPTCHA parece inválida. Verifique o formato.', 'asaas-easy-subscription-plugin'));
        }
        
        return sanitize_text_field($value);
    }
}

new Asaas_Admin_Settings();