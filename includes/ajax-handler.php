<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/class-form-processor.php';
// Certifique-se de que a constante NONCE_FIELD está definida em class-nonce-manager.php
// Exemplo: const NONCE_FIELD = 'asaas_nonce';
require_once ASAAS_PLUGIN_DIR . 'includes/security/class-nonce-manager.php';
require_once ASAAS_PLUGIN_DIR . 'includes/donation-log-functions.php';

/**
 * Função auxiliar para logging condicional
 * Só loga se WP_DEBUG estiver ativo
 */
function asaas_debug_log($message) {
    if (WP_DEBUG) {
        error_log($message);
    }
}

/**
 * Função helper para verificar token reCAPTCHA
 */
function verify_recaptcha_token($token, $secret) {
    asaas_debug_log('ASAAS: Iniciando verificação do reCAPTCHA com token (primeiros 15 caracteres): ' . substr($token, 0, 15));
    
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = wp_remote_post($verify_url, [
        'body' => [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : ''
        ],
        'timeout' => 15
    ]);

    if (is_wp_error($response)) {
        asaas_debug_log('ASAAS: Erro na comunicação com API reCAPTCHA: ' . $response->get_error_message());
        return false;
    }

    $result = json_decode(wp_remote_retrieve_body($response), true);
    $response_code = wp_remote_retrieve_response_code($response);
    
    asaas_debug_log('ASAAS: Resposta da API do reCAPTCHA (HTTP ' . $response_code . '): ' . json_encode($result));

    // Verificar sucesso básico
    if (!isset($result['success']) || !$result['success']) {
        $error_codes = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : 'desconhecido';
        asaas_debug_log('ASAAS: Falha na verificação do reCAPTCHA. Códigos de erro: ' . $error_codes);
        return false;
    }
    
    // Verificação adicional de score (apenas para v3)
    if (isset($result['score'])) {
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown_ip';
        $action = isset($result['action']) ? $result['action'] : '';
        
        asaas_debug_log('ASAAS: reCAPTCHA v3 score: ' . $result['score']);
        if ($result['score'] < 0.3) { // Score muito baixo - provável bot
            asaas_debug_log('ASAAS: Score reCAPTCHA muito baixo: ' . $result['score']);
            // Registrar no log de segurança
            asaas_log_recaptcha_score($ip_address, $result['score'], $action, true);
            asaas_log_blocked_ip($ip_address, ['recaptcha_score_muito_baixo']);
            return false;
        } else if ($result['score'] < 0.5) {
            // Score baixo mas não crítico - apenas log
            asaas_debug_log('ASAAS: Score reCAPTCHA baixo, mas aceitável: ' . $result['score']);
            asaas_log_recaptcha_score($ip_address, $result['score'], $action, false);
            asaas_log_suspicious_attempt($ip_address, ['recaptcha_score_baixo'], [
                'score' => $result['score'],
                'action' => $action
            ]);
        } else {
            // Score aceitável - registrar para análise
            asaas_log_recaptcha_score($ip_address, $result['score'], $action, false);
        }
    }
    
    asaas_debug_log('ASAAS: reCAPTCHA verificado com sucesso.');
    return true;
}

/**
 * Registra falha do reCAPTCHA para análise
 */
function asaas_log_recaptcha_failure($data) {
    $log_entry = sprintf(
        "[%s] reCAPTCHA Failure - Reason: %s, IP: %s, User-Agent: %s, Form: %s\n",
        date('Y-m-d H:i:s'),
        $data['reason'] ?? 'unknown',
        $data['ip'] ?? 'unknown',
        substr($data['user_agent'] ?? '', 0, 100),
        $data['form_type'] ?? 'unknown'
    );

    // Log em arquivo separado para facilitar análise
    $log_file = WP_CONTENT_DIR . '/asaas-recaptcha-failures.log';
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Registra o handler AJAX
 */
function asaas_register_ajax_handler() {
    add_action('wp_ajax_process_donation', 'asaas_process_donation');
    add_action('wp_ajax_nopriv_process_donation', 'asaas_process_donation');
    asaas_debug_log('ASAAS: Handlers AJAX registrados');
}
add_action('init', 'asaas_register_ajax_handler');

/**
 * Processa o formulário de doação
 */
function asaas_process_donation() {
    asaas_debug_log('ASAAS: Requisição AJAX recebida. HTTP Method: ' . (isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'N/A'));

    // 1. Garantir que é uma requisição POST e que contém dados
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($_POST)) {
        asaas_debug_log('ASAAS: Requisição não é POST ou não contém dados.');
        wp_send_json_error([
            'message' => __('Requisição inválida.', 'asaas-easy-subscription-plugin')
        ]);
        wp_die();
    }
    // Log das chaves POST recebidas para depuração (não logar valores diretamente por segurança)
    asaas_debug_log('ASAAS: Chaves POST recebidas: ' . json_encode(array_keys($_POST)));

    // 2. Verificação de Nonce (Primária e Obrigatória)
    // A constante NONCE_FIELD deve estar definida em Asaas_Nonce_Manager
    // Ex: public const NONCE_FIELD = 'asaas_nonce';
    $nonce_field_name = defined('Asaas_Nonce_Manager::NONCE_FIELD') ? Asaas_Nonce_Manager::NONCE_FIELD : 'asaas_nonce'; // Fallback
    
    $donation_type_action = isset($_POST['donation_type']) && sanitize_text_field(wp_unslash($_POST['donation_type'])) === 'recurring'
        ? Asaas_Nonce_Manager::ACTION_RECURRING_DONATION
        : Asaas_Nonce_Manager::ACTION_SINGLE_DONATION;

    asaas_debug_log('ASAAS: Verificando nonce - Campo esperado: ' . $nonce_field_name . ', Ação esperada: ' . $donation_type_action);
    asaas_debug_log('ASAAS: Nonce presente no POST: ' . (isset($_POST[$nonce_field_name]) ? 'Sim' : 'Não'));
    
    if (!isset($_POST[$nonce_field_name]) || !Asaas_Nonce_Manager::verify_nonce($_POST, $donation_type_action)) {
        asaas_debug_log('ASAAS: Falha na verificação do nonce. Campo esperado: ' . $nonce_field_name . ', Ação esperada: ' . $donation_type_action);
        
        // Log dos dados POST (sem dados sensíveis) para diagnóstico da falha do nonce
        $safe_post_for_log = $_POST;
        unset($safe_post_for_log['card_number'], $safe_post_for_log['ccv'], $safe_post_for_log[$nonce_field_name]); // Remove dados sensíveis e o próprio nonce
        asaas_debug_log('ASAAS: Dados POST (parciais) na falha do nonce: ' . json_encode(array_map('sanitize_text_field', $safe_post_for_log)));
        
        wp_send_json_error([
            'message' => __('Erro de segurança. Recarregue a página e tente novamente.', 'asaas-easy-subscription-plugin')
        ]);
        wp_die();
    }
    asaas_debug_log('ASAAS: Nonce (' . $nonce_field_name . ') verificado com sucesso para a ação: ' . $donation_type_action);

    // 3. Verificação do reCAPTCHA com fallback e tratamento de erros melhorado
    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
    $recaptcha_secret = get_option('asaas_recaptcha_secret_key');
    
    asaas_debug_log('ASAAS: Validando token reCAPTCHA. Token presente: ' . (!empty($recaptcha_token) ? 'Sim (' . strlen($recaptcha_token) . ' chars)' : 'Não'));
    asaas_debug_log('ASAAS: Chave secreta reCAPTCHA configurada: ' . (!empty($recaptcha_secret) ? 'Sim' : 'Não'));
    
    // Verificar se reCAPTCHA está configurado (site key + secret key)
    $recaptcha_configured = !empty($recaptcha_secret) && !empty(get_option('asaas_recaptcha_site_key'));
    
    if ($recaptcha_configured) {
        asaas_debug_log('ASAAS: reCAPTCHA configurado, executando verificação');
        
        // Novo: Verificação de chave válida
        if (!preg_match('/^6[A-Za-z0-9_-]{38}$/', $recaptcha_secret)) {
            asaas_debug_log('ASAAS: ALERTA - Formato da chave secreta reCAPTCHA parece inválido');
        }
        
        // Verificar se token foi fornecido
        if (empty($recaptcha_token)) {
            asaas_debug_log('ASAAS: Token reCAPTCHA não fornecido, mas reCAPTCHA está configurado');
            asaas_debug_log('ASAAS: Aplicando fallback - continuando sem reCAPTCHA mas logando');
            
            // Log da falha para análise
            asaas_log_recaptcha_failure([
                'reason' => 'token_missing',
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
                'form_type' => $donation_type_action
            ]);
            
            // Continuar processamento sem bloquear
            asaas_debug_log('ASAAS: Continuando processamento sem verificação reCAPTCHA');
        } else {
            // Executar verificação completa do reCAPTCHA
            $recaptcha_valid = verify_recaptcha_token($recaptcha_token, $recaptcha_secret);
            
            if (!$recaptcha_valid) {
                asaas_debug_log('ASAAS: Verificação reCAPTCHA falhou, aplicando fallback');
                asaas_log_recaptcha_failure([
                    'reason' => 'verification_failed',
                    'ip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown',
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
                    'form_type' => $donation_type_action
                ]);
                
                // Continuar processamento sem bloquear
                asaas_debug_log('ASAAS: Continuando processamento apesar da falha reCAPTCHA');
            } else {
                asaas_debug_log('ASAAS: reCAPTCHA verificado com sucesso');
            }
        }
    } else {
        asaas_debug_log('ASAAS: reCAPTCHA não configurado completamente. Pulando verificação.');
    }

    // 4. Medidas Anti-Bot Adicionais
    $suspicious = false;
    $reasons = [];

    // Verificação baseada no tempo de preenchimento
    if (isset($_POST['form_start_time'])) {
        $time_spent = time() - intval(sanitize_text_field(wp_unslash($_POST['form_start_time'])));
        asaas_debug_log("ASAAS: Tempo de preenchimento do formulário: {$time_spent} segundos");
        if ($time_spent < 3) { // Limiar pode ser ajustado
            $suspicious = true;
            $reasons[] = "preenchimento_muito_rapido ({$time_spent}s)";
        }
    }

    // Verificação de campos Honeypot
    if (!empty($_POST['website']) || !empty($_POST['email_confirm'])) {
        $suspicious = true;
        $reasons[] = "honeypot_preenchido";
        asaas_debug_log('ASAAS: Campos honeypot preenchidos - provável bot.');
    }

    // Verificação de velocidade de submissão por IP
    $ip_addr_for_velocity = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown_ip';
    $ip_cache_key = 'asaas_submissions_' . md5($ip_addr_for_velocity);
    $submission_count = (int) get_transient($ip_cache_key); // get_transient retorna false se não existir, (int) converte para 0
    
    asaas_debug_log("ASAAS: Submissões recentes deste IP ({$ip_addr_for_velocity}): {$submission_count}");
    if ($submission_count > 5) { // Limiar pode ser ajustado
        $suspicious = true;
        $reasons[] = "excesso_submissoes ({$submission_count})";
    }
    set_transient($ip_cache_key, $submission_count + 1, HOUR_IN_SECONDS);

    if ($suspicious) {
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown_ip';
        
        asaas_debug_log('ASAAS: Submissão suspeita bloqueada - Razões: ' . implode(', ', $reasons));
        
        // Registrar no log de segurança
        asaas_log_blocked_ip($ip_address, $reasons);
        
        // Registrar detalhes adicionais para análise
        $safe_data_for_log = [
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
            'referer' => isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '',
            'submission_count' => $submission_count ?? 0,
            'time_spent' => isset($time_spent) ? $time_spent : 'N/A'
        ];
        asaas_log_suspicious_attempt($ip_address, $reasons, $safe_data_for_log);
        
        wp_send_json_error([
            'message' => __('Sua solicitação parece automatizada. Por favor, tente novamente mais tarde.', 'asaas-easy-subscription-plugin')
        ]);
        wp_die();
    }
    asaas_debug_log('ASAAS: Verificações anti-bot adicionais passaram.');

    // 5. Processar Doação
    try {
        // A classe Asaas_Form_Processor é responsável pela sanitização detalhada dos campos do formulário.
        // Passamos $_POST diretamente, pois o processador fará a validação e sanitização.
        $form_data_to_process = $_POST;

        $processor = new Asaas_Form_Processor();
        $result = $processor->process_form($form_data_to_process);
        
        asaas_debug_log('ASAAS: Resultado do processamento pelo Form_Processor: ' . print_r($result, true));
        
        if (isset($result['success']) && $result['success']) {
            $responseData = ['data' => $result['data'] ?? []];
            $payment_method_from_form = isset($result['data']['payment_method']) ? $result['data']['payment_method'] : null;

            // Definir mensagem de sucesso e tipo de pagamento para o frontend
            switch ($payment_method_from_form) {
                case 'pix':
                    $responseData['message'] = (isset($result['data']['pix_code']) && !empty($result['data']['pix_code'])) // Checar se pix_code não é vazio
                        ? __('QR Code PIX gerado com sucesso', 'asaas-easy-subscription-plugin')
                        : __('Pagamento PIX iniciado, aguardando dados do QR Code.', 'asaas-easy-subscription-plugin');
                    if (!(isset($result['data']['pix_code']) && !empty($result['data']['pix_code']))) {
                        asaas_debug_log('ASAAS: Aviso - Dados do PIX (pix_code/pix_text) não encontrados ou vazios para pagamento PIX.');
                    }
                    $responseData['payment_type'] = 'PIX';
                    break;
                case 'boleto':
                    $responseData['message'] = (isset($result['data']['bank_slip_url']) && !empty($result['data']['bank_slip_url'])) // Checar se url não é vazia
                        ? __('Boleto gerado com sucesso', 'asaas-easy-subscription-plugin')
                        : __('Pagamento com Boleto iniciado, aguardando link.', 'asaas-easy-subscription-plugin');
                    if (!(isset($result['data']['bank_slip_url']) && !empty($result['data']['bank_slip_url']))) {
                         asaas_debug_log('ASAAS: Aviso - bank_slip_url não encontrado ou vazio para pagamento BOLETO.');
                    }
                    $responseData['payment_type'] = 'BOLETO';
                    break;
                case 'card':
                    $responseData['message'] = isset($result['data']['subscription_id'])
                        ? __('Assinatura criada com sucesso!', 'asaas-easy-subscription-plugin')
                        : __('Pagamento com cartão processado com sucesso', 'asaas-easy-subscription-plugin');
                    $responseData['payment_type'] = isset($result['data']['subscription_id']) ? 'RECURRING_CARD' : 'SINGLE_CARD';
                    break;
                default:
                    $responseData['message'] = __('Doação processada com sucesso', 'asaas-easy-subscription-plugin');
                    // O payment_type pode ser desconhecido ou não definido aqui.
            }
            wp_send_json_success($responseData);
        } else {
            wp_send_json_error([
                'message' => $result['message'] ?? __('Houve erros na sua submissão', 'asaas-easy-subscription-plugin'),
                'errors'  => $result['errors'] ?? ['Erro desconhecido ao processar o formulário.']
            ]);
        }
    } catch (Exception $e) {
        asaas_debug_log('ASAAS: Exceção no processamento da doação: ' . $e->getMessage() . ' Na linha: ' . $e->getLine() . ' Trace: ' . $e->getTraceAsString());
        wp_send_json_error([
            // Mensagem genérica para o usuário
            'message' => __('Ocorreu um erro inesperado ao processar sua doação. Por favor, tente novamente.', 'asaas-easy-subscription-plugin'),
            // Mensagem de desenvolvimento (pode ser removida ou condicionada a WP_DEBUG)
            // 'dev_message' => $e->getMessage() 
        ]);
    }
    
    wp_die(); // Termina a execução do WordPress para requisições AJAX
}
