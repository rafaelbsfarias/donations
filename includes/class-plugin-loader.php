<?php

if (!defined('ABSPATH')) {
    exit;
}

class Asaas_Plugin_Loader {
    public function init() {
        // Carregar arquivos do painel administrativo.       
        
        if (is_admin()) {
            require_once ASAAS_PLUGIN_DIR . 'admin/class-admin-settings.php';
            require_once ASAAS_PLUGIN_DIR . 'admin/class-admin-menu.php';                        
        }

        require_once ASAAS_PLUGIN_DIR . 'includes/enqueue-scripts.php';
        require_once ASAAS_PLUGIN_DIR . 'includes/shortcodes.php';
        require_once ASAAS_PLUGIN_DIR . 'includes/class-asaas-api.php';
        require_once ASAAS_PLUGIN_DIR . 'includes/ajax-handler.php'; 
        require_once ASAAS_PLUGIN_DIR . 'includes/donation-log-functions.php'; // Novas funções de log
    }
}