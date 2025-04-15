<?php
/*
Plugin Name: DonationSaaS
Description: Integração com a API do Asaas para pagamentos recorrentes.
Version: 2.0
Author: Rafael B. S. Farias
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

// Debugar detalhes da execução do arquivo
error_log("[DEBUG] main.php - Arquivo carregado às " . date('Y-m-d H:i:s') . " - Request URL: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A'));

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/EncryptionHelper.php';
require_once __DIR__ . '/create_customer.php';
require_once __DIR__ . '/SubscriptionService.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/UserForm.php';
require_once __DIR__ . '/UserController.php';

// Carrega o código da área administrativa com verificação de segurança
$admin_file = __DIR__ . '/admin/admin.php';
if (file_exists($admin_file)) {
    error_log("[DEBUG] main.php - Carregando admin.php");
    require_once $admin_file;
}

function donationsaas_shortcode() {
    error_log("[DEBUG] donationsaas_shortcode - Função chamada às " . date('Y-m-d H:i:s') . " - Backtrace:");
    
    // Obter backtrace para ver de onde a função shortcode está sendo chamada
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    foreach ($backtrace as $index => $call) {
        error_log("[DEBUG] donationsaas_shortcode - Backtrace #{$index}: " 
            . (isset($call['class']) ? $call['class'] . '::' : '') 
            . $call['function'] . ' - File: ' 
            . (isset($call['file']) ? $call['file'] : 'unknown') . ' Line: ' 
            . (isset($call['line']) ? $call['line'] : 'unknown'));
    }
    
    $asaasApi = new Asaas_API();
    $userService = new UserService($asaasApi);
    $userForm = new UserForm();
    $controller = new UserController($userService, $userForm);
    
    error_log("[DEBUG] donationsaas_shortcode - Instâncias criadas, chamando handleRequest");
    
    return $controller->handleRequest();
}

// Verificar se shortcode já existe antes de registrar
if (!shortcode_exists('donationsaas_form')) {
    error_log("[DEBUG] main.php - Registrando shortcode donationsaas_form");
    add_shortcode('donationsaas_form', 'donationsaas_shortcode');
} else {
    error_log("[DEBUG] main.php - Shortcode donationsaas_form já existe");
}
