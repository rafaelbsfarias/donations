<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/security/class-nonce-manager.php';

// Shortcode para doação recorrente
function asaas_recurring_donation_shortcode() {
    // Passa o tipo de formulário para o template
    $form_data = [
        'form_type' => 'recurring',
        'nonce_action' => Asaas_Nonce_Manager::ACTION_RECURRING_DONATION,
    ];
    ob_start();
    include ASAAS_PLUGIN_DIR . 'templates/form-recurring-donation.php';
    return ob_get_clean();
}
add_shortcode('asaas_recurring_donation', 'asaas_recurring_donation_shortcode');

// Shortcode para doação única
function asaas_single_donation_shortcode() {  
    // Passa o tipo de formulário para o template
    $form_data = [
        'form_type' => 'single',
        'nonce_action' => Asaas_Nonce_Manager::ACTION_SINGLE_DONATION,
    ];
    
    ob_start();
    include ASAAS_PLUGIN_DIR . 'templates/form-single-donation.php';
    return ob_get_clean();
}
add_shortcode('asaas_single_donation', 'asaas_single_donation_shortcode');