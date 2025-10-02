<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-form-sanitizer.php';
require_once ASAAS_PLUGIN_DIR . 'includes/class-asaas-api.php';

/**
 * Processador de formulários
 */
class Asaas_Form_Processor {
    /**
     * Processa dados do formulário
     * 
     * @param array $form_data Dados do formulário
     * @return array Resultado do processamento
     */
    public function process_form($form_data) {
        $sanitizer = new Asaas_Form_Sanitizer();
        $result = $sanitizer->sanitize_form($form_data);
        
        if (!$result['is_valid']) {
            return [
                'success' => false,
                'errors' => $result['errors']
            ];
        }
        
        try {
            // Inicializar API
            $api = new Asaas_API_Conn();
            $customers_api = $api->get_customers();
            
            // Mapear os dados do formulário para o formato esperado pela API
            $customer_data = [
                'name' => $result['sanitized']['full_name'],
                'cpfCnpj' => $result['sanitized']['cpf_cnpj'],
            ];
            
            // Adicionar campos opcionais se existirem
            if (!empty($result['sanitized']['email'])) {
                $customer_data['email'] = $result['sanitized']['email'];
            }
            
            if (!empty($result['sanitized']['phone'])) {
                $customer_data['phone'] = $result['sanitized']['phone'];
            }
            
            // Primeiro, verificar se o cliente já existe
            $customer_id = $customers_api->find_by_cpf_cnpj($customer_data['cpfCnpj']);
            
            // Se não existir, criar um novo
            if (!$customer_id) {
                $create_result = $customers_api->create_customer($customer_data);
                
                if (!$create_result['success']) {
                    return [
                        'success' => false,
                        'errors' => $create_result['errors']
                    ];
                }
                
                $customer_id = $create_result['data']['id'];
                $result['sanitized']['customer_created'] = true;
            } else {
                $result['sanitized']['customer_created'] = false;
            }
            
            // Armazenar o ID do cliente nos dados sanitizados
            $result['sanitized']['customer_id'] = $customer_id;
            
            // Se o método de pagamento for cartão, fazer a tokenização
            if (isset($form_data['payment_method']) && $form_data['payment_method'] === 'card') {
                // Verificar se todos os campos do cartão foram informados
                $required_card_fields = ['card_number', 'expiry_month', 'expiry_year', 'ccv'];
                $missing_fields = [];
                
                foreach ($required_card_fields as $field) {
                    if (empty($form_data[$field])) {
                        $missing_fields[] = $field;
                    }
                }
                
                if (!empty($missing_fields)) {
                    return [
                        'success' => false,
                        'errors' => ['Campos obrigatórios do cartão não informados: ' . implode(', ', $missing_fields)]
                    ];
                }
                
                // Dados do cartão
                $card_data = [
                    'holder_name' => $result['sanitized']['full_name'],
                    'number' => $form_data['card_number'],
                    'expiry_month' => $form_data['expiry_month'],
                    'expiry_year' => $form_data['expiry_year'],
                    'ccv' => $form_data['ccv']
                ];
                
                // Dados do titular
                $holder_info = [
                    'name' => $result['sanitized']['full_name'],
                    'email' => $result['sanitized']['email'],
                    'cpf_cnpj' => $result['sanitized']['cpf_cnpj'],
                    'postal_code' => isset($form_data['cep']) ? $form_data['cep'] : '',
                    'address_number' => isset($form_data['address_number']) ? $form_data['address_number'] : '',
                    'phone' => isset($form_data['phone']) ? $form_data['phone'] : ''
                ];
                
                // Tokenizar cartão
                $credit_cards_api = $api->get_credit_cards();
                $tokenization_result = $credit_cards_api->tokenize_credit_card($card_data, $holder_info, $customer_id);
                
                if (!$tokenization_result['success']) {
                    return [
                        'success' => false,
                        'errors' => $tokenization_result['errors']
                    ];
                }
                
                // Adicionar token do cartão aos dados sanitizados
                $result['sanitized']['card_token'] = $tokenization_result['data']['creditCardToken'];
                $result['sanitized']['card_brand'] = $tokenization_result['data']['creditCardBrand'];
                $result['sanitized']['card_last_digits'] = $tokenization_result['data']['creditCardNumber'];
            }
            
            // Verificar o tipo de doação e processar adequadamente
            if (isset($form_data['donation_type']) && $form_data['donation_type'] === 'recurring') {
                // Processar doação recorrente (assinatura)
                return $this->process_recurring_donation($api, $result['sanitized']);
            } else {
                // Processar doação única
                return $this->process_single_donation($api, $result['sanitized']);
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Processa uma doação recorrente (assinatura)
     * 
     * @param Asaas_API_Conn $api Conexão com a API
     * @param array $sanitized_data Dados sanitizados do formulário
     * @return array Resultado do processamento
     */
    private function process_recurring_donation($api, $sanitized_data) {
        // Verificar se temos os dados necessários
        if (empty($sanitized_data['customer_id']) || 
            empty($sanitized_data['donation_value']) || 
            empty($sanitized_data['card_token'])) {
            
            return [
                'success' => false,
                'errors' => ['Dados incompletos para criação da assinatura recorrente']
            ];
        }
        
        // Preparar dados da assinatura
        $subscription_data = [
            'customer' => $sanitized_data['customer_id'],
            'billingType' => 'CREDIT_CARD',
            'cycle' => 'MONTHLY',
            'description' => 'Doação recorrente',
            'value' => $sanitized_data['donation_value'],
            'nextDueDate' => date('Y-m-d'), 
            'creditCardToken' => $sanitized_data['card_token']
        ];
        
        // Criar assinatura
        $subscriptions_api = $api->get_subscriptions();
        $subscription_result = $subscriptions_api->create_subscription($subscription_data);
        
        if (!$subscription_result['success']) {
            return [
                'success' => false,
                'errors' => $subscription_result['errors']
            ];
        }
        
        // Adicionar detalhes da assinatura aos dados sanitizados
        $sanitized_data['subscription_id'] = $subscription_result['data']['id'];
        $sanitized_data['subscription_status'] = $subscription_result['data']['status'];
        $sanitized_data['next_due_date'] = $subscription_result['data']['nextDueDate'];
        
        return [
            'success' => true,
            'data' => $sanitized_data,
            'message' => 'Assinatura criada com sucesso!'
        ];
    }
    
    /**
     * Processa uma doação única
     * 
     * @param Asaas_API_Conn $api Conexão com a API
     * @param array $sanitized_data Dados sanitizados do formulário
     * @return array Resultado do processamento
     */
    private function process_single_donation($api, $sanitized_data) {
        // Capturar o valor formatado se disponível
        $formatted_value = isset($_POST['formatted_donation_value']) ? 
            sanitize_text_field($_POST['formatted_donation_value']) : 
            number_format($sanitized_data['donation_value'], 2, ',', '.');
        
        // Registrar tentativa de doação com o valor formatado
        if (function_exists('asaas_log_donation')) {
            asaas_log_donation(
                $sanitized_data['full_name'] ?? 'Nome não fornecido',
                $sanitized_data['donation_value'] ?? '0',
                'donation_attempt',
                $formatted_value
            );
        }
        
        // Verificar se temos os dados necessários
        if (empty($sanitized_data['customer_id']) || 
            empty($sanitized_data['donation_value'])) {
            
            return [
                'success' => false,
                'errors' => ['Dados incompletos para criação do pagamento']
            ];
        }
        
        // Preparar dados do pagamento
        $payment_data = [
            'customer' => $sanitized_data['customer_id'],
            'value' => $sanitized_data['donation_value'],
            'dueDate' => date('Y-m-d'),
            'description' => 'Doação única'
        ];
        
        // Adicionar campos específicos por método de pagamento
        if (!empty($sanitized_data['payment_method'])) {
            switch ($sanitized_data['payment_method']) {
                case 'pix':
                    $payment_data['billingType'] = 'PIX';
                    break;
                    
                case 'boleto':
                    $payment_data['billingType'] = 'BOLETO';
                    break;
                    
                case 'card':
                    $payment_data['billingType'] = 'CREDIT_CARD';
                    $payment_data['creditCardToken'] = $sanitized_data['card_token'];
                    break;
            }
        }
        
        // Registrar payload que será enviado para API
        if (function_exists('asaas_log_api_payload')) {
            asaas_log_api_payload(
                $payment_data, 
                'payments',
                $sanitized_data['customer_id']
            );
        }
        
        // Criar pagamento
        $payments_api = $api->get_payments();
        $payment_result = $payments_api->create_payment($payment_data);
        
        if (!$payment_result['success']) {
            // Registrar falha na API com o valor formatado
            if (function_exists('asaas_log_donation')) {
                asaas_log_donation(
                    $sanitized_data['full_name'] ?? 'Nome não fornecido',
                    $sanitized_data['donation_value'] ?? '0',
                    'api_error',
                    $formatted_value
                );
            }
            
            return [
                'success' => false,
                'errors' => $payment_result['errors']
            ];
        }
        
        // Registrar sucesso no processamento com o valor formatado
        if (function_exists('asaas_log_donation') && $payment_result['success']) {
            asaas_log_donation(
                $sanitized_data['full_name'] ?? 'Nome não fornecido',
                $payment_result['data']['value'] ?? $sanitized_data['donation_value'],
                'payment_success',
                $formatted_value
            );
        }
        
        // Para pagamentos PIX, obter os dados específicos
        if ($sanitized_data['payment_method'] === 'pix') {
            // Obter dados do QR Code do PIX
            $pix_result = $payments_api->get_pix_qrcode($payment_result['data']['id']);
            if ($pix_result['success']) {
                $sanitized_data['pix_code'] = $pix_result['data']['encodedImage'] ?? null;
                $sanitized_data['pix_text'] = $pix_result['data']['payload'] ?? null;
            }
        }
        
        // Adicionar detalhes do pagamento aos dados sanitizados
        $sanitized_data['payment_id'] = $payment_result['data']['id'];
        $sanitized_data['payment_status'] = $payment_result['data']['status'];
        $sanitized_data['due_date'] = $payment_result['data']['dueDate'];
        $sanitized_data['invoice_url'] = $payment_result['data']['invoiceUrl'] ?? null;
        $sanitized_data['bank_slip_url'] = $payment_result['data']['bankSlipUrl'] ?? null;
        $sanitized_data['nossoNumero'] = $payment_result['data']['nossoNumero'] ?? null;
        $sanitized_data['payment_method'] = $sanitized_data['payment_method']; // Manter o método de pagamento para o frontend
        
        return [
            'success' => true,
            'data' => $sanitized_data,
            'message' => 'Pagamento criado com sucesso!'
        ];
    }
}