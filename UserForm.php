<?php
class UserForm {
    private static $form_counter = 0;
    
    /**
     * Renderiza o formulário para cadastro do usuário.
     *
     * @return string HTML do formulário.
     */
    public function render() {
        self::$form_counter++;
        $form_id = self::$form_counter;
        
        error_log("[DEBUG] UserForm::render - Renderizando formulário #{$form_id}");
        
        // Obter backtrace para ver de onde a função está sendo chamada
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($backtrace as $index => $call) {
            error_log("[DEBUG] UserForm::render #{$form_id} - Backtrace #{$index}: " 
                . (isset($call['class']) ? $call['class'] . '::' : '') 
                . $call['function'] . ' - File: ' 
                . (isset($call['file']) ? $call['file'] : 'unknown') . ' Line: ' 
                . (isset($call['line']) ? $call['line'] : 'unknown'));
        }
        
        // Verificar se já temos uma submissão de formulário
        $is_post_request = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user_form']));
        error_log("[DEBUG] UserForm::render #{$form_id} - É uma requisição POST? " . ($is_post_request ? 'Sim' : 'Não'));
        
        $html = '<form method="post">';

        // Campo nonce para segurança
        $html .= wp_nonce_field('create_user_action', 'create_user_nonce', true, false);

        // Campos do cliente
        $html .= '<p><label>Nome: <input type="text" name="user_name" required></label></p>';
        $html .= '<p><label>Email: <input type="email" name="user_email" required></label></p>';
        $html .= '<p><label>CPF/CNPJ: <input type="text" name="user_cpfcnpj" required></label></p>';

        // Informações do titular do cartão
        $html .= '<p><label>Código Postal: <input type="text" name="user_postalCode" required></label></p>';
        $html .= '<p><label>Número do Endereço: <input type="text" name="user_addressNumber" required></label></p>';
        //$html .= '<p><label>Complemento do Endereço: <input type="text" name="user_addressComplement"></label></p>';
        $html .= '<p><label>Telefone: <input type="text" name="user_phone" required></label></p>';
        //$html .= '<p><label>Celular: <input type="text" name="user_mobilePhone"></label></p>';

        // Detalhes do cartão
        $html .= '<p><label>Número do Cartão: <input type="text" name="user_cardNumber" required></label></p>';
        $html .= '<p><label>Mês de Validade: <input type="text" name="user_expiryMonth" required></label></p>';
        $html .= '<p><label>Ano de Validade: <input type="text" name="user_expiryYear" required></label></p>';
        $html .= '<p><label>Código de Segurança (CCV): <input type="text" name="user_ccv" required></label></p>';

        $html .= '<p><input type="submit" name="submit_user_form" value="Criar Cliente"></p>';
        $html .= '</form>';
        
        error_log("[DEBUG] UserForm::render #{$form_id} - Formulário renderizado");

        return $html;
    }
}
