<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-data-sanitizer.php';

/**
 * Classe responsável pelas operações relacionadas a pagamentos na API Asaas
 */
class Asaas_API_Payments {
    /**
     * Cliente da API
     *
     * @var Asaas_API_Client
     */
    private $client;
    
    /**
     * Construtor
     *
     * @param Asaas_API_Client $client Cliente da API
     */
    public function __construct(Asaas_API_Client $client) {
        $this->client = $client;
    }
    
    /**
     * Cria um novo pagamento único
     *
     * @param array $payment_data Dados do pagamento
     * @return array Resultado da operação
     */
    public function create_payment($payment_data) {
        // Verificar se todos os dados necessários estão presentes
        if (empty($payment_data['customer']) || 
            empty($payment_data['value']) || 
            empty($payment_data['dueDate']) ||
            empty($payment_data['billingType'])) {
            
            return [
                'success' => false,
                'errors' => ['Dados incompletos para criação do pagamento']
            ];
        }
        
        // Para pagamentos com cartão de crédito, validar o token
        if ($payment_data['billingType'] === 'CREDIT_CARD' && empty($payment_data['creditCardToken'])) {
            return [
                'success' => false,
                'errors' => ['Token do cartão de crédito não informado']
            ];
        }
        
        // Sanitizar o valor (se necessário)
        if (isset($payment_data['value']) && is_string($payment_data['value'])) {
            $payment_data['value'] = Asaas_Data_Sanitizer::sanitize_float($payment_data['value']);
        }
        
        // Fazer a requisição para criar o pagamento
        $response = $this->client->post('payments', $payment_data);
        
        // Verificar se houve erro na requisição
        if (isset($response['errors'])) {
            $error_messages = [];
            foreach ($response['errors'] as $error) {
                $error_messages[] = isset($error['description']) ? $error['description'] : 'Erro desconhecido';
            }
            
            return [
                'success' => false,
                'errors' => $error_messages
            ];
        }
        
        // Se não teve erro e obteve ID, retorna sucesso
        if (isset($response['id'])) {
            return [
                'success' => true,
                'data' => $response
            ];
        }
        
        // Caso não identifique sucesso nem erro específico
        return [
            'success' => false,
            'errors' => ['Erro não identificado ao criar pagamento']
        ];
    }

    /**
     * Obtém o QR Code PIX para um pagamento
     *
     * @param string $payment_id ID do pagamento
     * @return array Resultado da operação
     */
    public function get_pix_qrcode($payment_id) {
        if (empty($payment_id)) {
            return [
                'success' => false,
                'errors' => ['ID do pagamento não fornecido']
            ];
        }
        
        // Fazer a requisição para obter o QR Code do PIX
        $response = $this->client->get("payments/{$payment_id}/pixQrCode");
        
        // Verificar se houve erro na requisição
        if (isset($response['errors'])) {
            $error_messages = [];
            foreach ($response['errors'] as $error) {
                $error_messages[] = isset($error['description']) ? $error['description'] : 'Erro desconhecido';
            }
            
            return [
                'success' => false,
                'errors' => $error_messages
            ];
        }
        
        // Se tiver os dados do PIX, retorna sucesso
        if (isset($response['encodedImage'])) {
            return [
                'success' => true,
                'data' => $response
            ];
        }
        
        // Caso não identifique sucesso nem erro específico
        return [
            'success' => false,
            'errors' => ['Erro não identificado ao obter QR Code PIX']
        ];
    }
}