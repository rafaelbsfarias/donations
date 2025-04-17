<?php
/*
Plugin Name: DonationSaaS
Description: Integração com a API do Asaas para pagamentos recorrentes e únicos.
Version: 2.0
Author: Rafael B. S. Farias
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

// === INCLUDES GERAIS ===
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/EncryptionHelper.php';
require_once __DIR__ . '/create_customer.php';
require_once __DIR__ . '/SubscriptionService.php';

// === MÓDULO: PAGAMENTO RECORRENTE ===
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/UserForm.php';
require_once __DIR__ . '/UserController.php';

// Carrega admin.php se existir
$admin_file = __DIR__ . '/admin/admin.php';
if (file_exists($admin_file)) {
    require_once $admin_file;
}

// === SHORTCODE: donationsaas_form (pagamento recorrente) ===
function donationsaas_shortcode() {
    $asaasApi = new Asaas_API();
    $userService = new UserService($asaasApi);
    $userForm = new UserForm();
    $controller = new UserController($userService, $userForm);

    return $controller->handleRequest();
}

if (!shortcode_exists('donationsaas_form')) {
    add_shortcode('donationsaas_form', 'donationsaas_shortcode');
}

// === MÓDULO: PAGAMENTO ÚNICO ===
require_once __DIR__ . '/PagamentoUnico/PagamentoUnicoService.php';
require_once __DIR__ . '/PagamentoUnico/formulario-pagamento.php';
require_once __DIR__ . '/PagamentoUnico/PagamentoUnicoController.php';

function pagamento_unico_shortcode() {
    $service = new PagamentoUnicoService();
    $formulario = new FormularioPagamento();
    $controller = new PagamentoUnicoController($service, $formulario);

    return $controller->handleRequest();
}

if (!shortcode_exists('pagamento_unico_form')) {
    add_shortcode('pagamento_unico_form', 'pagamento_unico_shortcode');
}
