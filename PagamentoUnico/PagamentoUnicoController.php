<?php
class PagamentoUnicoController {
    private $pagamentoUnicoService;
    private $formularioPagamento;

    public function __construct(PagamentoUnicoService $service, FormularioPagamento $formulario) {
        $this->pagamentoUnicoService = $service;
        $this->formularioPagamento = $formulario;
    }

    public function handleRequest() {
        $output = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pagamento_unico'])) {
            if (!isset($_POST['pagamento_unico_nonce']) || !wp_verify_nonce($_POST['pagamento_unico_nonce'], 'criar_pagamento_unico')) {
                return '<p>Falha na verificação de segurança.</p>';
            }

            // Sanitiza os dados recebidos
            $name = sanitize_text_field($_POST['nome']);
            $email = sanitize_email($_POST['email']);
            $cpfCnpj = sanitize_text_field($_POST['cpfCnpj']);
            $value = floatval($_POST['valor']);
            $billingType = sanitize_text_field($_POST['forma_pagamento']);
            $description = "Pagamento único"; // Valor padrão se não for fornecido
            
            // Sanitiza os campos de CEP, endereço e telefone
            $postalCode = sanitize_text_field($_POST['postalCode']);
            $phone = sanitize_text_field($_POST['phone']);
            $addressNumber = sanitize_text_field($_POST['addressNumber']); // Novo campo

            // Dados do cliente
            $dadosCliente = [
                'name' => $name,
                'email' => $email,
                'cpfCnpj' => $cpfCnpj,
            ];

            $cliente = $this->pagamentoUnicoService->criarCliente($dadosCliente);

            if (!$cliente['success'] || !isset($cliente['data']['id'])) {
                return '<p>Erro ao criar cliente.</p>';
            }

            $dadosCobranca = [
                'customer' => $cliente['data']['id'],
                'billingType' => $billingType,
                'value' => $value,
                'dueDate' => date('Y-m-d'),
                'description' => $description,
            ];

            // Cartão
            if ($billingType === 'CREDIT_CARD') {
                $dadosCobranca['creditCard'] = [
                    'holderName' => $name,
                    'number' => sanitize_text_field($_POST['cardNumber']),
                    'expiryMonth' => sanitize_text_field($_POST['expiryMonth']),
                    'expiryYear' => sanitize_text_field($_POST['expiryYear']),
                    'ccv' => sanitize_text_field($_POST['ccv'])
                ];

                $dadosCobranca['creditCardHolderInfo'] = [
                    'name' => $name,
                    'email' => $email,
                    'cpfCnpj' => $cpfCnpj,
                    'postalCode' => $postalCode,
                    'phone' => $phone,
                    'addressNumber' => $addressNumber // Adicionando o número do endereço
                ];
            }

            $cobranca = $this->pagamentoUnicoService->criarCobranca($dadosCobranca);

            if ($cobranca['success'] && isset($cobranca['data']['id'])) {
                $output .= '<h3>Pagamento criado com sucesso!</h3>';
                
                if (isset($cobranca['data']['invoiceUrl'])) {
                    $output .= '<p><a href="' . esc_url($cobranca['data']['invoiceUrl']) . '" target="_blank" class="button button-primary">Acessar boleto/pagamento</a></p>';
                }
                
                if (isset($cobranca['data']['status'])) {
                    $output .= '<p>Status: ' . esc_html($cobranca['data']['status']) . '</p>';
                }
            } else {
                $output .= '<p>Erro ao criar pagamento.</p>';
                if (isset($cobranca['error'])) {
                    $output .= '<pre>' . print_r($cobranca['error'], true) . '</pre>';
                }
            }
        }

        $output .= $this->formularioPagamento->render();

        return $output;
    }
}