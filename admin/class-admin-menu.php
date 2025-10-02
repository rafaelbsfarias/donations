<?php

if (!defined('ABSPATH')) {
    exit;
}

class Asaas_Admin_Menu {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Asaas Settings', 'asaas-easy-subscription-plugin'),
            __('Asaas Plugin', 'asaas-easy-subscription-plugin'),
            'manage_options',
            'asaas-settings',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic',
            80
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Asaas Plugin Settings', 'asaas-easy-subscription-plugin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('asaas_plugin_settings');
                do_settings_sections('asaas-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

new Asaas_Admin_Menu();