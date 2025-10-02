<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ASAAS_PLUGIN_DIR . 'includes/sanitization/class-data-sanitizer.php';

/**
 * Classe para sanitização de formulários
 */
class Asaas_Form_Sanitizer {
    /**
     * Sanitiza e valida um array de dados do formulário
     *
     * @param array $form_data Dados do formulário
     * @return array Resultado com dados sanitizados e erros
     */
    public function sanitize_form($form_data) {
        $sanitized = [];
        $errors = [];
        
        // Nome completo
        if (!empty($form_data['full_name'])) {
            $sanitized['full_name'] = Asaas_Data_Sanitizer::sanitize_text($form_data['full_name']);
            if (mb_strlen($sanitized['full_name']) > 50) {
                $errors['full_name'] = __('O nome deve ter menos de 50 caracteres.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['full_name'] = __('Please enter your full name', 'asaas-easy-subscription-plugin');
        }
        
        // Email
        if (!empty($form_data['email'])) {
            $sanitized['email'] = Asaas_Data_Sanitizer::sanitize_email($form_data['email']);
            if (!is_email($sanitized['email'])) {
                $errors['email'] = __('Por favor, insira um endereço de e-mail válido.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['email'] = __('Please enter your email address', 'asaas-easy-subscription-plugin');
        }
        
        // CPF/CNPJ
        if (!empty($form_data['cpf_cnpj'])) {
            $sanitized['cpf_cnpj'] = Asaas_Data_Sanitizer::sanitize_cpf_cnpj($form_data['cpf_cnpj']);
            $length = strlen($sanitized['cpf_cnpj']);
            if ($length !== 11 && $length !== 14) {
                $errors['cpf_cnpj'] = __('Por favor, insira um CPF válido (11 dígitos) ou CNPJ válido (14 dígitos).', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['cpf_cnpj'] = __('Please enter your CPF or CNPJ', 'asaas-easy-subscription-plugin');
        }
        
        // Valor da doação
        if (!empty($form_data['donation_value'])) {
            $sanitized['donation_value'] = Asaas_Data_Sanitizer::sanitize_float($form_data['donation_value']);
            
            if ($sanitized['donation_value'] < 5) {
                $errors['donation_value'] = __('O valor da doação deve ser de no mínimo R$5.', 'asaas-easy-subscription-plugin');
            } elseif ($sanitized['donation_value'] > 100000) {
                $errors['donation_value'] = __('O valor da doação deve ser de no máximo R$100.000.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['donation_value'] = __('Por favor, insira o valor da doação.', 'asaas-easy-subscription-plugin');
        }
        
        // Método de pagamento
        if (!empty($form_data['payment_method'])) {
            $sanitized['payment_method'] = Asaas_Data_Sanitizer::sanitize_text($form_data['payment_method']);
            if (!in_array($sanitized['payment_method'], ['pix', 'boleto', 'card'])) {
                $errors['payment_method'] = __('Invalid payment method', 'asaas-easy-subscription-plugin');
            }
            
            // Se for cartão, validar os campos relacionados
            if ($sanitized['payment_method'] === 'card') {
                $this->validate_card_fields($form_data, $sanitized, $errors);
            }
        } else {
            $errors['payment_method'] = __('Please select a payment method', 'asaas-easy-subscription-plugin');
        }
        
        return [
            'sanitized' => $sanitized,
            'errors' => $errors,
            'is_valid' => empty($errors)
        ];
    }
    
    /**
     * Valida os campos de cartão de crédito
     *
     * @param array $form_data Dados do formulário
     * @param array &$sanitized Array de dados sanitizados (referência)
     * @param array &$errors Array de erros (referência)
     */
    private function validate_card_fields($form_data, &$sanitized, &$errors) {
        // Número do cartão
        if (!empty($form_data['card_number'])) {
            $sanitized['card_number'] = Asaas_Data_Sanitizer::sanitize_card_number($form_data['card_number']);
            if (strlen($sanitized['card_number']) < 13 || strlen($sanitized['card_number']) > 16) {
                $errors['card_number'] = __('Por favor, insira um número de cartão válido.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['card_number'] = __('Please enter the card number', 'asaas-easy-subscription-plugin');
        }
        
        // Mês de validade
        if (!empty($form_data['expiry_month'])) {
            $sanitized['expiry_month'] = Asaas_Data_Sanitizer::sanitize_numbers_only($form_data['expiry_month']);
            $month = (int) $sanitized['expiry_month'];
            if ($month < 1 || $month > 12) {
                $errors['expiry_month'] = __('Por favor, insira um mês de validade válido. (1-12)', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['expiry_month'] = __('Please enter the expiry month', 'asaas-easy-subscription-plugin');
        }
        
        // Ano de validade
        if (!empty($form_data['expiry_year'])) {
            $sanitized['expiry_year'] = Asaas_Data_Sanitizer::sanitize_numbers_only($form_data['expiry_year']);
            $year = (int) $sanitized['expiry_year'];
            $current_year = (int) date('Y');
            if ($year < $current_year || $year > $current_year + 20) {
                $errors['expiry_year'] = __('Por favor, insira um ano de validade válido.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['expiry_year'] = __('Please enter the expiry year', 'asaas-easy-subscription-plugin');
        }
        
        // CCV
        if (!empty($form_data['ccv'])) {
            $sanitized['ccv'] = Asaas_Data_Sanitizer::sanitize_numbers_only($form_data['ccv']);
            if (strlen($sanitized['ccv']) < 3 || strlen($sanitized['ccv']) > 4) {
                $errors['ccv'] = __('Por favor, insira um código de segurança (CCV) válido.', 'asaas-easy-subscription-plugin');
            }
        } else {
            $errors['ccv'] = __('Please enter the security code (CCV)', 'asaas-easy-subscription-plugin');
        }
        
        // CEP (Obrigatório para cartão)
        if (!empty($form_data['cep'])) {
            $sanitized['cep'] = Asaas_Data_Sanitizer::sanitize_postal_code($form_data['cep']);
            if (strlen($sanitized['cep']) !== 8) {
                $errors['cep'] = __('Por favor, insira um código postal (CEP) válido.', 'asaas-easy-subscription-plugin');
            }
        } else if ($form_data['payment_method'] === 'card') {
            $errors['cep'] = __('Please enter your postal code', 'asaas-easy-subscription-plugin');
        }
        
        // Número do endereço (Obrigatório para cartão)
        if (!empty($form_data['address_number'])) {
            $sanitized['address_number'] = Asaas_Data_Sanitizer::sanitize_text($form_data['address_number']);
        } else if ($form_data['payment_method'] === 'card') {
            $errors['address_number'] = __('Por favor, insira o número do seu endereço.', 'asaas-easy-subscription-plugin');
        }
        
        // Telefone (Obrigatório para cartão)
        if (!empty($form_data['phone'])) {
            $sanitized['phone'] = Asaas_Data_Sanitizer::sanitize_phone($form_data['phone']);
            if (strlen($sanitized['phone']) < 10 || strlen($sanitized['phone']) > 11) {
                $errors['phone'] = __('Por favor, insira um número de telefone válido com código de área.', 'asaas-easy-subscription-plugin');
            }
        } else if ($form_data['payment_method'] === 'card') {
            $errors['phone'] = __('Please enter your phone number', 'asaas-easy-subscription-plugin');
        }
    }
}