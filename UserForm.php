<?php
class UserForm {
    /**
     * Renderiza o formulário para cadastro do usuário.
     *
     * @return string HTML do formulário.
     */
    public function render() {
        $html = '<form method="post">';
        // Campo nonce para segurança
        $html .= wp_nonce_field('create_user_action', 'create_user_nonce', true, false);
        $html .= '<p><label>Nome: <input type="text" name="user_name" required></label></p>';
        $html .= '<p><label>Email: <input type="email" name="user_email" required></label></p>';
        $html .= '<p><label>CPF/CNPJ: <input type="text" name="user_cpfcnpj" required></label></p>';
        $html .= '<p><input type="submit" name="submit_user_form" value="Criar Cliente"></p>';
        $html .= '</form>';
        return $html;
    }
}
