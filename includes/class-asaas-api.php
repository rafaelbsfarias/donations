<?php

// Verificar se a constante ABSPATH está definida
if (!defined('ABSPATH')) {
    exit;
}

// Carregar as classes e interfaces necessárias
require_once ASAAS_PLUGIN_DIR . 'api/interfaces/interface-http-client.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-wordpress-http-client.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-asaas-api-client.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-asaas-api-customers.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-asaas-api-credit-cards.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-asaas-api-subscriptions.php';
require_once ASAAS_PLUGIN_DIR . 'api/class-asaas-api-payments.php';

// Verificar se a classe já existe antes de tentar declará-la
if (!class_exists('Asaas_API_Conn')) {
    class Asaas_API_Conn {
        /**
         * Cliente da API
         *
         * @var Asaas_API_Client
         */
        private $api_client;
        
        /**
         * Cliente de operações com clientes
         *
         * @var Asaas_API_Customers
         */
        private $customers;
        
        /**
         * Cliente de operações com cartões de crédito
         *
         * @var Asaas_API_Credit_Cards
         */
        private $credit_cards;
        
        /**
         * Cliente de operações com assinaturas
         *
         * @var Asaas_API_Subscriptions
         */
        private $subscriptions;
        
        /**
         * Cliente de operações com pagamentos
         *
         * @var Asaas_API_Payments
         */
        private $payments;
        
        /**
         * URL base da API (mantida para compatibilidade)
         *
         * @var string
         */
        private $api_base_url;
        
        /**
         * Token de acesso (mantido para compatibilidade)
         *
         * @var string
         */
        private $access_token;
        
        /**
         * Construtor com injeção de dependência opcional
         *
         * @param Asaas_HTTP_Client_Interface|null $http_client Cliente HTTP opcional
         * @throws Exception Se as configurações necessárias não estiverem disponíveis
         */
        public function __construct($http_client = null) {
            //Recupera a URL base da API usando a função do Admin Settings
            if (!class_exists('Asaas_Admin_Settings')) {
                throw new Exception('A classe Asaas_Admin_Settings não foi carregada.');
            }
            
            $this->api_base_url = Asaas_Admin_Settings::get_api_base_url();
            if (!$this->api_base_url) {
                throw new Exception('A URL base da API do Asaas não está configurada no painel administrativo.');
            }
            
            // Recupera a chave de API salva no banco de dados
            $this->access_token = get_option('asaas_api_key');
            if (!$this->access_token) {
                throw new Exception('A chave de API do Asaas não está configurada no painel administrativo.');
            }
            
            // Use o cliente HTTP fornecido ou crie um novo cliente WordPress
            if (!$http_client) {
                $http_client = new Asaas_WordPress_HTTP_Client();
            }
            
            // Inicializar o cliente da API e as classes auxiliares
            $this->api_client = new Asaas_API_Client($this->api_base_url, $this->access_token, $http_client);
            $this->customers = new Asaas_API_Customers($this->api_client);
            $this->credit_cards = new Asaas_API_Credit_Cards($this->api_client);
            $this->subscriptions = new Asaas_API_Subscriptions($this->api_client);
            $this->payments = new Asaas_API_Payments($this->api_client);
        }
        
        /**
         * Obtém o objeto de operações com clientes
         *
         * @return Asaas_API_Customers
         */
        public function get_customers() {
            return $this->customers;
        }
        
        /**
         * Obtém o objeto de operações com cartões de crédito
         *
         * @return Asaas_API_Credit_Cards
         */
        public function get_credit_cards() {
            return $this->credit_cards;
        }
        
        /**
         * Obtém o objeto de operações com assinaturas
         *
         * @return Asaas_API_Subscriptions
         */
        public function get_subscriptions() {
            return $this->subscriptions;
        }
        
        /**
         * Obtém o objeto de operações com pagamentos
         *
         * @return Asaas_API_Payments
         */
        public function get_payments() {
            return $this->payments;
        }

        /**
         * Obtém o cliente da API
         * 
         * @return Asaas_API_Client
         */
        public function get_api_client() {
            return $this->api_client;
        }
    }
}
