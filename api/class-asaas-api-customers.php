<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-data-sanitizer.php';

/**
 * Classe responsável pelas operações relacionadas a clientes na API Asaas
 */
class Asaas_API_Customers {
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
     * Busca um cliente pelo CPF/CNPJ
     *
     * @param string $cpf_cnpj CPF ou CNPJ do cliente
     * @return string|null ID do cliente ou null se não encontrado
     */
    public function find_by_cpf_cnpj($cpf_cnpj) {
        // Sanitiza o CPF/CNPJ removendo caracteres não-numéricos
        $cpf_cnpj = Asaas_Data_Sanitizer::sanitize_cpf_cnpj($cpf_cnpj);
        
        // Obtém os dados do cliente
        $response = $this->client->get('customers', ['cpfCnpj' => $cpf_cnpj]);
        
        // Verifica se o cliente foi encontrado
        if (isset($response['data']) && is_array($response['data']) && count($response['data']) > 0) {
            return $response['data'][0]['id'];
        }
        
        return null;
    }
    
    /**
     * Cria um novo cliente no Asaas
     *
     * @param array $customer_data Dados do cliente (nome, cpfCnpj, etc)
     * @return array Resposta da API (sucesso ou erro)
     */
    public function create_customer($customer_data) {
        // Garantir que os campos obrigatórios estejam presentes
        if (empty($customer_data['name']) || empty($customer_data['cpfCnpj'])) {
            return [
                'success' => false,
                'errors' => ['Campos obrigatórios não informados (nome e CPF/CNPJ)']
            ];
        }
        
        // Sanitiza o CPF/CNPJ antes de enviar
        $customer_data['cpfCnpj'] = Asaas_Data_Sanitizer::sanitize_cpf_cnpj($customer_data['cpfCnpj']);
        
        // Faz a requisição para criar o cliente
        $response = $this->client->post('customers', $customer_data);
        
        // Verifica se houve erro na requisição
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
            'errors' => ['Erro não identificado ao criar cliente']
        ];
    }
}