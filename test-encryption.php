<?php
/*
 * Plugin Name: DonationSaaS - Test Encryption
 * Description: Testa a criptografia do DonationSaaS
 * Version: 1.0
 * Author: Rafael B. S. Farias
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/EncryptionHelper.php';

// Verifique se a chave secreta está definida
if (!defined('SECRET_ENCRYPTION_KEY')) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p><strong>DonationSaaS Teste:</strong> A constante SECRET_ENCRYPTION_KEY não está definida no wp-config.php. A criptografia não funcionará!</p></div>';
    });
} else {
    add_action('admin_notices', function() {
        echo '<div class="updated"><p><strong>DonationSaaS Teste:</strong> A constante SECRET_ENCRYPTION_KEY está definida.</p></div>';
    });
}

// Verificar configurações salvas
add_action('admin_notices', function() {
    $donationsaas_key = get_option('donationsaas_api_key_encrypted', '');
    $donations_key = get_option('donations_api_key_encrypted', '');
    
    echo '<div class="notice notice-info"><p>';
    echo '<strong>DonationSaaS Teste:</strong><br>';
    echo 'donationsaas_api_key_encrypted: ' . (empty($donationsaas_key) ? 'Não definida' : 'Definida (' . strlen($donationsaas_key) . ' caracteres)') . '<br>';
    echo 'donations_api_key_encrypted: ' . (empty($donations_key) ? 'Não definida' : 'Definida (' . strlen($donations_key) . ' caracteres)') . '<br>';
    
    if (!empty($donationsaas_key)) {
        try {
            $decrypted = EncryptionHelper::decrypt($donationsaas_key);
            echo 'Descriptografia funcionou! Primeiros 5 caracteres: ' . substr($decrypted, 0, 5) . '...';
        } catch (Exception $e) {
            echo 'Erro ao descriptografar: ' . $e->getMessage();
        }
    }
    
    echo '</p></div>';
});