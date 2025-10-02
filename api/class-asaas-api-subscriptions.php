<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-data-sanitizer.php';

/**
 * Classe responsável pelas operações relacionadas a assinaturas na API Asaas
 */
class Asaas_API_Subscriptions {
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
     * Cria uma nova assinatura recorrente
     *
     * @param array $subscription_data Dados da assinatura
     * @return array Resultado da operação
     */
    public function create_subscription($subscription_data) {
        // Verificar se todos os dados necessários estão presentes
        if (empty($subscription_data['customer']) || 
            empty($subscription_data['value']) || 
            empty($subscription_data['nextDueDate']) ||
            empty($subscription_data['billingType'])) {
            
            return [
                'success' => false,
                'errors' => ['Dados incompletos para criação da assinatura']
            ];
        }
        
        // Para pagamentos com cartão de crédito, validar o token
        if ($subscription_data['billingType'] === 'CREDIT_CARD' && empty($subscription_data['creditCardToken'])) {
            return [
                'success' => false,
                'errors' => ['Token do cartão de crédito não informado']
            ];
        }
        
        // Sanitizar o valor (se necessário)
        if (isset($subscription_data['value']) && is_string($subscription_data['value'])) {
            $subscription_data['value'] = Asaas_Data_Sanitizer::sanitize_float($subscription_data['value']);
        }
        
        // Fazer a requisição para criar a assinatura
        $response = $this->client->post('subscriptions', $subscription_data);
        
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
            'errors' => ['Erro não identificado ao criar assinatura']
        ];
    }
    
    /**
     * Cancela uma assinatura existente
     *
     * @param string $subscription_id ID da assinatura
     * @return array Resultado da operação
     */
    public function cancel_subscription($subscription_id) {
        if (empty($subscription_id)) {
            return [
                'success' => false,
                'errors' => ['ID da assinatura não informado']
            ];
        }
        
        $response = $this->client->post("subscriptions/{$subscription_id}/cancel", []);
        
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
        
        // Se não houve erro, retorna sucesso
        if (isset($response['id']) && $response['status'] === 'INACTIVE') {
            return [
                'success' => true,
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'errors' => ['Erro não identificado ao cancelar assinatura']
        ];
    }
}