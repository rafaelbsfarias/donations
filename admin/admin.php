<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../EncryptionHelper.php';

class DonationSaaS_Admin {
    private $settings;

    public function __construct() {
        $this->settings = new Settings();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'process_form_submission'));
    }

    /**
     * Adiciona o item de menu no painel do WordPress
     */
    public function add_admin_menu() {
        add_menu_page(
            'Configurações DonationSaaS', // Título da página
            'DonationSaaS',              // Título do menu
            'manage_options',            // Capacidade mínima
            'donationsaas',              // Slug do menu
            array($this, 'settings_page'), // Função que renderiza a página
            'dashicons-money',
            30
        );
    }

    /**
     * Registra as configurações
     */
    public function register_settings() {
        register_setting('donationsaas_settings_group', 'donationsaas_api_key_encrypted');
        register_setting('donationsaas_settings_group', 'donationsaas_base_url');
        register_setting('donationsaas_settings_group', 'donationsaas_environment');
    }

    /**
     * Processa o envio do formulário de configurações
     */
    public function process_form_submission() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'donationsaas' || !isset($_POST['submit'])) {
            return;
        }

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'donationsaas_settings_group-options')) {
            add_settings_error(
                'donationsaas_messages',
                'donationsaas_error',
                'Falha na verificação de segurança.',
                'error'
            );
            return;
        }

        if (isset($_POST['donationsaas_api_key']) && isset($_POST['donationsaas_base_url']) && isset($_POST['donationsaas_environment'])) {
            $api_key = sanitize_text_field($_POST['donationsaas_api_key']);
            $base_url = esc_url_raw($_POST['donationsaas_base_url']);
            $environment = sanitize_text_field($_POST['donationsaas_environment']);

            if (!empty($api_key)) {
                try {
                    $encrypted_api_key = EncryptionHelper::encrypt($api_key);
                    update_option('donationsaas_api_key_encrypted', $encrypted_api_key);
                } catch (Exception $e) {
                    add_settings_error(
                        'donationsaas_messages',
                        'donationsaas_error',
                        'Erro ao criptografar a API key: ' . $e->getMessage(),
                        'error'
                    );
                    return;
                }
            }

            update_option('donationsaas_base_url', $base_url);
            update_option('donationsaas_environment', $environment);

            $this->update_env_file($api_key, $base_url);

            add_settings_error(
                'donationsaas_messages',
                'donationsaas_success',
                'Configurações salvas com sucesso.',
                'success'
            );
        }
    }

    /**
     * Renderiza a página de configurações
     */
    public function settings_page() {
        $encrypted_api_key = get_option('donationsaas_api_key_encrypted', '');
        $api_key = '';
        
        if (!empty($encrypted_api_key)) {
            try {
                $api_key = EncryptionHelper::decrypt($encrypted_api_key);
            } catch (Exception $e) {
                add_settings_error(
                    'donationsaas_messages',
                    'donationsaas_error',
                    'Erro ao descriptografar a API key: ' . $e->getMessage(),
                    'error'
                );
            }
        }
        
        $base_url = get_option('donationsaas_base_url', 'https://api-sandbox.asaas.com/v3/');
        $environment = get_option('donationsaas_environment', 'sandbox');

        settings_errors('donationsaas_messages');
        ?>
        <div class="wrap">
            <h1>Configurações DonationSaaS</h1>
            <form method="post" action="">
                <?php settings_fields('donationsaas_settings_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Ambiente</th>
                        <td>
                            <select name="donationsaas_environment" id="donationsaas_environment" onchange="updateBaseUrl()">
                                <option value="sandbox" <?php selected($environment, 'sandbox'); ?>>Sandbox (Testes)</option>
                                <option value="production" <?php selected($environment, 'production'); ?>>Produção</option>
                                <option value="custom" <?php selected($environment, 'custom'); ?>>Personalizado</option>
                            </select>
                            <p class="description">Selecione o ambiente da API Asaas.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">URL Base da API</th>
                        <td>
                            <input type="text" name="donationsaas_base_url" id="donationsaas_base_url" value="<?php echo esc_attr($base_url); ?>" class="regular-text">
                            <p class="description">Ex: https://api-sandbox.asaas.com/v3/</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="donationsaas_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                            <p class="description">Insira a API key do Asaas. Ela será criptografada automaticamente.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <script>
                function updateBaseUrl() {
                    const environment = document.getElementById('donationsaas_environment').value;
                    const baseUrlField = document.getElementById('donationsaas_base_url');
                    
                    if (environment === 'sandbox') {
                        baseUrlField.value = 'https://api-sandbox.asaas.com/v3/';
                        baseUrlField.readOnly = true;
                    } else if (environment === 'production') {
                        baseUrlField.value = 'https://api.asaas.com/v3/';
                        baseUrlField.readOnly = true;
                    } else {
                        baseUrlField.readOnly = false;
                    }
                }
                document.addEventListener('DOMContentLoaded', function() {
                    updateBaseUrl();
                });
            </script>
        </div>
        <?php
    }
    
    /**
     * Atualiza o arquivo .env com as configurações
     */
    private function update_env_file($api_key, $base_url) {
        $env_file = dirname(__DIR__) . '/.env';
        $env_content = "API_KEY={$api_key}\nBASE_URL={$base_url}\n";
        
        try {
            $dir = dirname($env_file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($env_file, $env_content);
            return true;
        } catch (Exception $e) {
            add_settings_error(
                'donationsaas_messages',
                'donationsaas_error',
                'Erro ao atualizar o arquivo .env: ' . $e->getMessage(),
                'error'
            );
            return false;
        }
    }
}

// Inicializa a classe
new DonationSaaS_Admin();