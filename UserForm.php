<?php
class UserForm {
    private static $form_counter = 0;

    public function render() {
        self::$form_counter++;
        $form_id = self::$form_counter;

        $html = '<style>
        .formulario-recorrente {
            max-width: 600px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-family: "Segoe UI", sans-serif;
        }
        .formulario-recorrente h2 {
            color: #0056d2;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .formulario-recorrente label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
            color: #333;
        }
        .formulario-recorrente input[type="text"],
        .formulario-recorrente input[type="email"],
        .formulario-recorrente input[type="number"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        .formulario-recorrente input[type="submit"] {
            margin-top: 2rem;
            background-color: #0056d2;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            display: block;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        .formulario-recorrente input[type="submit"]:hover {
            background-color: #0045b0;
        }
        </style>';

        $html .= '<form method="post" class="formulario-recorrente">';
        $html .= '<h2>Doação Mensal</h2>';
        $html .= '<p>Preencha as informações abaixo para cadastrar uma doação recorrente. Fique tranquilo, essa modalidade de doação não ocupa o limite do seu cartão de credito.</p>';
        $html .= wp_nonce_field('create_user_action', 'create_user_nonce', true, false);

        $html .= '<label>Nome:<input type="text" name="user_name" required></label>';
        $html .= '<label>Email:<input type="email" name="user_email" required></label>';
        $html .= '<label>CPF/CNPJ:<input type="text" name="user_cpfcnpj" required></label>';

        $html .= '<label>Código Postal:<input type="text" name="user_postalCode" required></label>';
        $html .= '<label>Número do Endereço:<input type="text" name="user_addressNumber" required></label>';
        $html .= '<label>Telefone:<input type="text" name="user_phone" required></label>';
        
        $html .= '<p><label> Valor da Doação:<input type="number" name="user_value" step="0.01" min="1" required></label></p>';
        $html .= '<label>Número do Cartão:<input type="text" name="user_cardNumber" required></label>';
        $html .= '<label>Mês de Validade:<input type="text" name="user_expiryMonth" required></label>';
        $html .= '<label>Ano de Validade:<input type="text" name="user_expiryYear" required></label>';
        $html .= '<label>CCV:<input type="text" name="user_ccv" required></label>';

        $html .= '<input type="submit" name="submit_user_form" value="Doar Agora">';
        $html .= '</form>';

        return $html;
    }
}