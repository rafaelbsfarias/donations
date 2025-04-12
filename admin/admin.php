<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Asaas_Admin {
    private $settings;

    public function __construct() {
        require_once dirname(__DIR__) . '/settings.php';
        $this->settings = new Settings();
        
        // Adiciona hooks para o painel admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Adiciona item de menu no painel administrativo
     */
    public function add_admin_menu() {
        add_menu_page(
            'Configurações Asaas',
            'Asaas',
            'manage_options',
            'asaas-settings',
            array($this, 'settings_page'),
            'dashicons-money',
            30
        );
    }

    /**
     * Registra as configurações
     */
    public function register_settings() {
        register_setting('asaas-settings-group', 'asaas_api_key');
        register_setting('asaas-settings-group', 'asaas_base_url');
    }

    /**
     * Renderiza a página de configurações
     */
    public function settings_page() {
        // Verifica se o formulário foi enviado
        if (isset($_POST['save_asaas_settings'])) {
            if (isset($_POST['asaas_api_key']) && isset($_POST['asaas_base_url'])) {
                $api_key = sanitize_text_field($_POST['asaas_api_key']);
                $base_url = sanitize_text_field($_POST['asaas_base_url']);
                
                // Salva as configurações no WordPress
                update_option('asaas_api_key', $api_key);
                update_option('asaas_base_url', $base_url);
                
                // Atualiza o arquivo .env
                $this->update_env_file($api_key, $base_url);
                
                echo '<div class="updated"><p>Configurações salvas com sucesso!</p></div>';
            }
        }

        // Carrega os valores atuais
        $api_key = get_option('asaas_api_key', $this->settings->get('API_KEY', ''));
        $base_url = get_option('asaas_base_url', $this->settings->get('BASE_URL', 'https://api-sandbox.asaas.com/v3/'));
        
        ?>
        <div class="wrap">
            <h1>Configurações Asaas</h1>
            <form method="post" action="">
                <?php settings_fields('asaas-settings-group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Ambiente</th>
                        <td>
                            <select name="asaas_environment" id="asaas_environment" onchange="updateBaseUrl()">
                                <option value="sandbox" <?php selected($base_url, 'https://api-sandbox.asaas.com/v3/'); ?>>Sandbox (Testes)</option>
                                <option value="production" <?php selected($base_url, 'https://api.asaas.com/v3/'); ?>>Produção</option>
                                <option value="custom">Personalizado</option>
                            </select>
                            <p class="description">Selecione o ambiente da API Asaas.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">URL da API</th>
                        <td>
                            <input type="text" name="asaas_base_url" id="asaas_base_url" value="<?php echo esc_attr($base_url); ?>" class="regular-text">
                            <p class="description">URL base da API Asaas (ex: https://api-sandbox.asaas.com/v3/)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Chave da API</th>
                        <td>
                            <input type="text" name="asaas_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                            <p class="description">Chave da API Asaas. Consulte sua conta Asaas para obter essa chave.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="save_asaas_settings" class="button-primary" value="Salvar Configurações">
                </p>
            </form>
        </div>
        <script>
            function updateBaseUrl() {
                const environment = document.getElementById('asaas_environment').value;
                const baseUrlField = document.getElementById('asaas_base_url');
                
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
            
            // Executar ao carregar a página
            document.addEventListener('DOMContentLoaded', function() {
                const baseUrl = document.getElementById('asaas_base_url').value;
                const environmentSelect = document.getElementById('asaas_environment');
                
                if (baseUrl === 'https://api-sandbox.asaas.com/v3/') {
                    environmentSelect.value = 'sandbox';
                } else if (baseUrl === 'https://api.asaas.com/v3/') {
                    environmentSelect.value = 'production';
                } else {
                    environmentSelect.value = 'custom';
                }
                
                updateBaseUrl();
            });
        </script>
        <?php
    }

    /**
     * Atualiza o arquivo .env com as novas configurações
     */
    private function update_env_file($api_key, $base_url) {
        $env_file = dirname(__DIR__) . '/.env';
        $env_content = "API_KEY={$api_key}\nBASE_URL={$base_url}\n";
        
        // Escreve no arquivo .env
        file_put_contents($env_file, $env_content);
    }
}

// Inicia a classe de administração
$asaas_admin = new Asaas_Admin();