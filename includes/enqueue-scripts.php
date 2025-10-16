<?php
if (!defined('ABSPATH')) {
    exit;
}

function asaas_enqueue_scripts() {
    // Use um caminho base consistente
    $plugin_url = plugin_dir_url(dirname(__FILE__)); // Vai para o diretório pai de includes
    
    // Registrar estilos
    wp_register_style('asaas-form-style', $plugin_url . 'assets/frontend/css/form-style.css', [], '1.1.0');
    
    // Registrar scripts na ordem correta de dependências
    wp_register_script('asaas-form-utils', $plugin_url . 'assets/frontend/js/form-utils.js', ['jquery'], '1.1.0', true);
    wp_register_script('asaas-form-masks', $plugin_url . 'assets/frontend/js/form-masks.js', ['jquery', 'asaas-form-utils'], '1.1.0', true);
    wp_register_script('asaas-form-ui', $plugin_url . 'assets/frontend/js/form-ui.js', ['jquery', 'asaas-form-utils'], '1.1.0', true);
    
    // Carregar reCAPTCHA primeiro se configurado
    $recaptcha_site_key = get_option('asaas_recaptcha_site_key', '');
    $recaptcha_secret_key = get_option('asaas_recaptcha_secret_key', '');
    $recaptcha_configured = !empty($recaptcha_site_key) && !empty($recaptcha_secret_key);
    
    if ($recaptcha_configured) {
        // Carregar script do reCAPTCHA da Google
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_site_key, [], null, false);
        
        // Registrar script do reCAPTCHA do plugin
        wp_register_script('asaas-form-recaptcha', $plugin_url . 'assets/frontend/js/form-recaptcha.js', ['jquery', 'google-recaptcha'], '1.1.0', true);
        
        // Localizar reCAPTCHA
        wp_localize_script('asaas-form-recaptcha', 'asaasRecaptcha', [
            'siteKey' => $recaptcha_site_key,
            'version' => 'v3',
            'configured' => true
        ]);
    } else {
        // Registrar script do reCAPTCHA sem dependência (fallback)
        wp_register_script('asaas-form-recaptcha', $plugin_url . 'assets/frontend/js/form-recaptcha.js', ['jquery'], '1.1.0', true);
        
        // Localizar reCAPTCHA (não configurado)
        wp_localize_script('asaas-form-recaptcha', 'asaasRecaptcha', [
            'siteKey' => '',
            'version' => 'v3',
            'configured' => false
        ]);
    }
    
    // Registrar script AJAX (depende de reCAPTCHA)
    wp_register_script('asaas-form-ajax', $plugin_url . 'assets/frontend/js/form-ajax.js', ['jquery', 'asaas-form-utils', 'asaas-form-ui', 'asaas-form-recaptcha'], '1.1.0', true);
    
    // Registrar script principal (depende de todos os outros)
    wp_register_script('asaas-form-script', $plugin_url . 'assets/frontend/js/form-script.js', ['jquery', 'asaas-form-utils', 'asaas-form-masks', 'asaas-form-ui', 'asaas-form-ajax', 'asaas-form-recaptcha'], '1.1.0', true);
    
    // Enfileirar tudo
    wp_enqueue_style('asaas-form-style');
    wp_enqueue_script('jquery');
    wp_enqueue_script('asaas-form-utils');
    wp_enqueue_script('asaas-form-masks');
    wp_enqueue_script('asaas-form-ui');
    wp_enqueue_script('asaas-form-recaptcha');
    wp_enqueue_script('asaas-form-ajax');
    wp_enqueue_script('asaas-form-script');
    
    // Localizar o script AJAX
    wp_localize_script('asaas-form-ajax', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('asaas_nonce')
    ]);
    
    // Verificar caminhos para debug
    if (WP_DEBUG) {
        error_log('Plugin URL base: ' . $plugin_url);
        error_log('reCAPTCHA configurado: ' . ($recaptcha_configured ? 'Sim' : 'Não'));
        error_log('Form Utils Path: ' . $plugin_url . 'assets/frontend/js/form-utils.js');
    }
}
add_action('wp_enqueue_scripts', 'asaas_enqueue_scripts');
