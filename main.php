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
    require_once $admin_file;
}

function donationsaas_shortcode() {
    $asaasApi = new Asaas_API();
    $userService = new UserService($asaasApi);
    $userForm = new UserForm();
    $controller = new UserController($userService, $userForm);
    return $controller->handleRequest();
}
add_shortcode('donationsaas_form', 'donationsaas_shortcode');
