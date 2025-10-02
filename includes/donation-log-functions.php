<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verifica se o log de doações está habilitado
 * 
 * @return bool
 */
function asaas_is_donation_log_enabled() {
    return get_option('asaas_enable_donation_logs') == 1;
}

/**
 * Garante que o diretório de logs existe e está protegido
 * 
 * @return string Caminho do diretório de logs
 */
function asaas_ensure_log_directory_exists() {
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/asaas-logs';
    
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
        
        // Criar arquivo .htaccess para proteger o diretório
        file_put_contents($log_dir . '/.htaccess', "Order deny,allow\nDeny from all");
        
        // Criar arquivo index.php vazio para evitar listagem de diretório
        file_put_contents($log_dir . '/index.php', '<?php // Silence is golden');
    }
    
    return $log_dir;
}

/**
 * Função simples para registrar logs de doação
 * 
 * @param string $donor_name Nome do doador
 * @param mixed $donation_value Valor da doação (já convertido para API)
 * @param string $stage Estágio do processamento
 * @param string $formatted_value Valor formatado como visto pelo usuário (opcional)
 * @return bool Sucesso ou falha
 */
function asaas_log_donation($donor_name, $donation_value, $stage = 'form_submission', $formatted_value = '') {
    // Verificar se os logs estão habilitados
    if (!asaas_is_donation_log_enabled()) {
        return false;
    }
    
    try {
        // Garantir que o diretório de logs existe
        $log_dir = asaas_ensure_log_directory_exists();
        
        // Definir arquivo de log (um por dia usando GMT)
        $log_file = $log_dir . '/donations-' . gmdate('Y-m-d') . '.log';
        
        // Adicionar offset do WordPress (se disponível)
        $time_offset = get_option('gmt_offset', 0);
        $time_string = gmdate('Y-m-d H:i:s', time() + ($time_offset * HOUR_IN_SECONDS));
        
        // Formatar entrada de log
        $log_entry = '[' . $time_string . '] ';
        $log_entry .= "[{$stage}] ";
        $log_entry .= "Nome: " . sanitize_text_field($donor_name) . " | ";
        
        // Incluir ambos os valores se o valor formatado estiver disponível
        if (!empty($formatted_value)) {
            $log_entry .= "Valor formatado: " . sanitize_text_field($formatted_value) . " | ";
            $log_entry .= "Valor convertido: " . sanitize_text_field($donation_value) . "\n";
        } else {
            $log_entry .= "Valor: " . sanitize_text_field($donation_value) . "\n";
        }
        
        // Registrar no arquivo
        return (file_put_contents($log_file, $log_entry, FILE_APPEND) !== false);
    } catch (Exception $e) {
        // Em caso de erro, registrar no log do WordPress
        error_log('Erro ao registrar log de doação: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registra o payload enviado para a API Asaas
 * 
 * @param array $payload Dados enviados para a API
 * @param string $endpoint Endpoint da API (payments, subscriptions, etc)
 * @param string $customer_id ID do cliente na Asaas
 * @return bool Sucesso ou falha
 */
function asaas_log_api_payload($payload, $endpoint, $customer_id = '') {
    // Verificar se os logs estão habilitados
    if (!asaas_is_donation_log_enabled()) {
        return false;
    }
    
    try {
        // Garantir que o diretório de logs existe
        $log_dir = asaas_ensure_log_directory_exists();
        
        // Definir arquivo de log para payload da API
        $log_file = $log_dir . '/api-payloads-' . gmdate('Y-m-d') . '.log';
        
        // Adicionar offset do WordPress (se disponível)
        $time_offset = get_option('gmt_offset', 0);
        $time_string = gmdate('Y-m-d H:i:s', time() + ($time_offset * HOUR_IN_SECONDS));
        
        // Extrair valor do payload se disponível
        $value = isset($payload['value']) ? $payload['value'] : 'N/A';
        
        // Formatar entrada de log
        $log_entry = '[' . $time_string . '] ';
        $log_entry .= "[API:{$endpoint}] ";
        $log_entry .= "Customer ID: " . sanitize_text_field($customer_id) . " | ";
        $log_entry .= "Valor: " . sanitize_text_field($value) . "\n";
        $log_entry .= "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        $log_entry .= "----------------------------------------\n";
        
        // Registrar no arquivo
        return (file_put_contents($log_file, $log_entry, FILE_APPEND) !== false);
    } catch (Exception $e) {
        // Em caso de erro, registrar no log do WordPress
        error_log('Erro ao registrar payload da API: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registra tentativas suspeitas de pagamento
 * 
 * @param string $ip_address Endereço IP
 * @param array $reasons Razões para suspeita
 * @param array $request_data Dados relevantes da requisição (sanitizados)
 * @return bool Sucesso ou falha
 */
function asaas_log_suspicious_attempt($ip_address, $reasons, $request_data = []) {
    // Verificar se os logs estão habilitados
    if (!asaas_is_donation_log_enabled()) {
        return false;
    }
    
    try {
        // Garantir que o diretório de logs existe
        $log_dir = asaas_ensure_log_directory_exists();
        
        // Definir arquivo de log para tentativas suspeitas
        $log_file = $log_dir . '/security-' . gmdate('Y-m-d') . '.log';
        
        // Adicionar offset do WordPress
        $time_offset = get_option('gmt_offset', 0);
        $time_string = gmdate('Y-m-d H:i:s', time() + ($time_offset * HOUR_IN_SECONDS));
        
        // Formatar entrada de log
        $log_entry = '[' . $time_string . '] ';
        $log_entry .= "[SUSPEITO] ";
        $log_entry .= "IP: " . sanitize_text_field($ip_address) . " | ";
        $log_entry .= "Razões: " . implode(', ', array_map('sanitize_text_field', $reasons)) . "\n";
        
        // Adicionar dados relevantes da requisição
        if (!empty($request_data)) {
            $safe_data = array_map('sanitize_text_field', $request_data);
            $log_entry .= "Dados: " . json_encode($safe_data) . "\n";
        }
        
        $log_entry .= "----------------------------------------\n";
        
        // Registrar no arquivo
        return (file_put_contents($log_file, $log_entry, FILE_APPEND) !== false);
    } catch (Exception $e) {
        error_log('Erro ao registrar tentativa suspeita: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registra IPs bloqueados e atualiza contador de bloqueios
 * 
 * @param string $ip_address Endereço IP
 * @param array $reasons Razões para bloqueio
 * @return bool Sucesso ou falha
 */
function asaas_log_blocked_ip($ip_address, $reasons) {
    // Verificar se os logs estão habilitados
    if (!asaas_is_donation_log_enabled()) {
        return false;
    }
    
    try {
        // Garantir que o diretório de logs existe
        $log_dir = asaas_ensure_log_directory_exists();
        
        // Definir arquivo de log para IPs bloqueados
        $log_file = $log_dir . '/blocked-ips.log';
        
        // Adicionar offset do WordPress
        $time_offset = get_option('gmt_offset', 0);
        $time_string = gmdate('Y-m-d H:i:s', time() + ($time_offset * HOUR_IN_SECONDS));
        
        // Formatar entrada de log
        $log_entry = '[' . $time_string . '] ';
        $log_entry .= "[BLOQUEADO] ";
        $log_entry .= "IP: " . sanitize_text_field($ip_address) . " | ";
        $log_entry .= "Razões: " . implode(', ', array_map('sanitize_text_field', $reasons)) . "\n";
        
        // Atualizar contador na opção do WordPress
        $blocked_ips = get_option('asaas_blocked_ips', []);
        if (!isset($blocked_ips[$ip_address])) {
            $blocked_ips[$ip_address] = [
                'count' => 0,
                'first_block' => time(),
                'reasons' => []
            ];
        }
        
        $blocked_ips[$ip_address]['count']++;
        $blocked_ips[$ip_address]['last_block'] = time();
        $blocked_ips[$ip_address]['reasons'] = array_unique(array_merge(
            $blocked_ips[$ip_address]['reasons'],
            $reasons
        ));
        
        update_option('asaas_blocked_ips', $blocked_ips);
        
        // Registrar no arquivo
        return (file_put_contents($log_file, $log_entry, FILE_APPEND) !== false);
    } catch (Exception $e) {
        error_log('Erro ao registrar IP bloqueado: ' . $e->getMessage());
        return false;
    }
}

/**
 * Registra score reCAPTCHA para análise
 * 
 * @param string $ip_address Endereço IP
 * @param float $score Score do reCAPTCHA
 * @param string $action Ação do reCAPTCHA
 * @param bool $blocked Se o usuário foi bloqueado
 * @return bool Sucesso ou falha
 */
function asaas_log_recaptcha_score($ip_address, $score, $action = '', $blocked = false) {
    // Verificar se os logs estão habilitados
    if (!asaas_is_donation_log_enabled()) {
        return false;
    }
    
    try {
        // Garantir que o diretório de logs existe
        $log_dir = asaas_ensure_log_directory_exists();
        
        // Definir arquivo de log para scores reCAPTCHA
        $log_file = $log_dir . '/recaptcha-' . gmdate('Y-m-d') . '.log';
        
        // Adicionar offset do WordPress
        $time_offset = get_option('gmt_offset', 0);
        $time_string = gmdate('Y-m-d H:i:s', time() + ($time_offset * HOUR_IN_SECONDS));
        
        // Formatar entrada de log
        $log_entry = '[' . $time_string . '] ';
        $log_entry .= $blocked ? "[BLOQUEADO] " : "[INFO] ";
        $log_entry .= "IP: " . sanitize_text_field($ip_address) . " | ";
        $log_entry .= "Score: " . number_format($score, 2) . " | ";
        if (!empty($action)) {
            $log_entry .= "Ação: " . sanitize_text_field($action) . " | ";
        }
        $log_entry .= "Status: " . ($blocked ? "Bloqueado" : "Permitido") . "\n";
        
        // Registrar no arquivo
        return (file_put_contents($log_file, $log_entry, FILE_APPEND) !== false);
    } catch (Exception $e) {
        error_log('Erro ao registrar score reCAPTCHA: ' . $e->getMessage());
        return false;
    }
}