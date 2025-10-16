<?php

/**
 * Plugin Name: Asaas Easy Subscription Plugin
 * Description: Integração com o Asaas para pagamentos únicos e recorrentes.
 * Version: 1.1.0
 * Author: Rafael
 * Text Domain: asaas-easy-subscription-plugin
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('ASAAS_PLUGIN_VERSION', '1.1.0');
define('ASAAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASAAS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ASAAS_PLUGIN_DIR . 'includes/class-plugin-loader.php';

// Initialize the plugin.
function asaas_easy_subscription_plugin_init() {
    $plugin_loader = new Asaas_Plugin_Loader();
    $plugin_loader->init();
}
add_action('plugins_loaded', 'asaas_easy_subscription_plugin_init');