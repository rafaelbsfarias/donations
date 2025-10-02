<?php
// Debug script para verificar configurações do plugin
if (!defined('ABSPATH')) {
    exit;
}

echo "<h2>Debug Asaas Plugin</h2>";

// Verificar configurações
$api_key = get_option('asaas_api_key', '');
$recaptcha_site = get_option('asaas_recaptcha_site_key', '');
$recaptcha_secret = get_option('asaas_recaptcha_secret_key', '');

echo "<h3>Configurações:</h3>";
echo "<ul>";
echo "<li>API Key: " . (empty($api_key) ? '<span style="color:red">Não configurada</span>' : '<span style="color:green">Configurada (' . strlen($api_key) . ' chars)</span>') . "</li>";
echo "<li>reCAPTCHA Site Key: " . (empty($recaptcha_site) ? '<span style="color:red">Não configurada</span>' : '<span style="color:green">Configurada (' . strlen($recaptcha_site) . ' chars)</span>') . "</li>";
echo "<li>reCAPTCHA Secret Key: " . (empty($recaptcha_secret) ? '<span style="color:red">Não configurada</span>' : '<span style="color:green">Configurada (' . strlen($recaptcha_secret) . ' chars)</span>') . "</li>";
echo "</ul>";

// Verificar se scripts estão enfileirados
echo "<h3>Scripts Enfileirados:</h3>";
global $wp_scripts;
$asaas_scripts = ['asaas-form-recaptcha', 'asaas-form-ajax', 'google-recaptcha'];
echo "<ul>";
foreach ($asaas_scripts as $script) {
    $registered = isset($wp_scripts->registered[$script]);
    $enqueued = isset($wp_scripts->queue[$script]);
    echo "<li>$script: " . ($registered ? 'Registrado' : '<span style="color:red">Não registrado</span>') . " / " . ($enqueued ? 'Enfileirado' : '<span style="color:orange">Não enfileirado</span>') . "</li>";
}
echo "</ul>";

// Verificar localized scripts
echo "<h3>Scripts Localizados:</h3>";
$localized = $wp_scripts->get_data('asaas-form-recaptcha', 'data');
echo "<pre>";
if ($localized) {
    echo "asaas-form-recaptcha data:\n";
    echo htmlspecialchars($localized);
} else {
    echo "Nenhum dado localizado encontrado para asaas-form-recaptcha";
}
echo "</pre>";

// Verificar constantes
echo "<h3>Constantes do Plugin:</h3>";
echo "<ul>";
echo "<li>ASAAS_PLUGIN_DIR: " . (defined('ASAAS_PLUGIN_DIR') ? ASAAS_PLUGIN_DIR : '<span style="color:red">Não definida</span>') . "</li>";
echo "<li>WP_DEBUG: " . (WP_DEBUG ? 'Ativo' : 'Inativo') . "</li>";
echo "</ul>";