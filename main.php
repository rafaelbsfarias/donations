<?php
/*
Plugin Name: DonationSaaS
Description: Integração com a API do Asaas para pagamentos recorrentes.
Version: 1.0
Author: Rafael B. S. Farias
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/create_customer.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/UserForm.php';
require_once __DIR__ . '/UserController.php';

// Carregar arquivo de administração apenas no painel admin
if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

/**
 * Shortcode para exibir o formulário de criação de cliente.
 */
function donationsaas_shortcode() {
    // Instancia a API do Asaas
    $asaasApi = new Asaas_API();
    // Cria o serviço de usuário, injetando a dependência da API
    $userService = new UserService($asaasApi);
    // Instancia o formulário
    $userForm = new UserForm();
    // Cria o controlador, injetando o serviço e o formulário
    $controller = new UserController($userService, $userForm);

    return $controller->handleRequest();
}
add_shortcode('donationsaas_form', 'donationsaas_shortcode');