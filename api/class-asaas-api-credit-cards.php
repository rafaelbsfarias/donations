<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-data-sanitizer.php';

/**
 * Classe responsável pelas operações relacionadas a cartões de crédito na API Asaas
 */
class Asaas_API_Credit_Cards {
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
     * Tokeniza um cartão de crédito
     *
     * @param array $card_data Dados do cartão
     * @param array $holder_info Informações do titular
     * @param string $customer_id ID do cliente no Asaas
     * @return array Resultado da tokenização
     */
    public function tokenize_credit_card($card_data, $holder_info, $customer_id) {
        // Verificar se todos os dados necessários estão presentes
        if (empty($card_data) || empty($holder_info) || empty($customer_id)) {
            return [
                'success' => false,
                'errors' => ['Dados incompletos para tokenização do cartão']
            ];
        }
        
        // Preparar os dados para a API
        $request_data = [
            'creditCard' => [
                'holderName' => $card_data['holder_name'],
                'number' => Asaas_Data_Sanitizer::sanitize_card_number($card_data['number']),
                'expiryMonth' => $card_data['expiry_month'],
                'expiryYear' => $card_data['expiry_year'],
                'ccv' => $card_data['ccv']
            ],
            'creditCardHolderInfo' => [
                'name' => $holder_info['name'],
                'email' => $holder_info['email'],
                'cpfCnpj' => Asaas_Data_Sanitizer::sanitize_cpf_cnpj($holder_info['cpf_cnpj']),
                'postalCode' => Asaas_Data_Sanitizer::sanitize_postal_code($holder_info['postal_code']),
                'addressNumber' => $holder_info['address_number'],
                'phone' => Asaas_Data_Sanitizer::sanitize_phone($holder_info['phone'])
            ],
            'customer' => $customer_id
        ];
        
        // Fazer a chamada à API
        $response = $this->client->post('creditCard/tokenizeCreditCard', $request_data);
        
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
        
        // Se obteve o token, retorna sucesso
        if (isset($response['creditCardToken'])) {
            return [
                'success' => true,
                'data' => [
                    'creditCardToken' => $response['creditCardToken'],
                    'creditCardNumber' => $response['creditCardNumber'],
                    'creditCardBrand' => $response['creditCardBrand']
                ]
            ];
        }
        
        // Caso não identifique sucesso nem erro específico
        return [
            'success' => false,
            'errors' => ['Erro não identificado ao tokenizar cartão']
        ];
    }
}