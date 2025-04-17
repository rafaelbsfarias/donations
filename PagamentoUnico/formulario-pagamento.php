<?php
require_once __DIR__ . '/../settings.php';

class FormularioPagamento {
    public function render() {
        ob_start();
        ?><style>
        .formulario-pagamento {
            max-width: 600px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', sans-serif;
        }
        .formulario-pagamento h2 {
            color: #0056d2;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .formulario-pagamento label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
            color: #333;
        }
        .formulario-pagamento input,
        .formulario-pagamento select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        .formulario-pagamento button {
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
        .formulario-pagamento button:hover {
            background-color: #0045b0;
        }
        </style>
        <form method="POST" action="" class="formulario-pagamento">
            <?php wp_nonce_field('criar_pagamento_unico', 'pagamento_unico_nonce'); ?>
            <h2>Doação</h2><br><br>
            <p>Preencha as informações abaixo para realizar sua doação.<p><br><br>
            <label for="nome">Nome:</label><br>
            <input type="text" name="nome" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" name="email" required><br><br>
            
            <label for="cpfCnpj">CPF ou CNPJ:</label><br>
            <input type="text" name="cpfCnpj" required><br><br>

            <label for="valor">Valor:</label><br>
            <input type="number" name="valor" min="1" step="0.01" required><br><br>

            <label for="forma_pagamento">Forma de Pagamento:</label><br>
            <select name="forma_pagamento" id="forma_pagamento" required onchange="mostrarCamposPagamento(this.value)">
                <option value="BOLETO">Boleto</option>
                <option value="PIX">Pix</option>
                <option value="CREDIT_CARD">Cartão de Crédito</option>
            </select><br><br>

            <div id="cartao_campos" style="display:none">
                <label for="cardNumber">Número do Cartão:</label><br>
                <input type="text" name="cardNumber" required><br><br>

                <label for="expiryMonth">Mês de Validade:</label><br>
                <input type="text" name="expiryMonth" placeholder="MM" required><br><br>

                <label for="expiryYear">Ano de Validade:</label><br>
                <input type="text" name="expiryYear" placeholder="AAAA" required><br><br>

                <label for="ccv">Código de Segurança:</label><br>
                <input type="text" name="ccv" required><br><br>
                
                <!-- Campo de CEP do titular -->
                <label for="postalCode">CEP do Titular:</label><br>
                <input type="text" name="postalCode" placeholder="00000-000" required><br><br>
                
                <!-- Campo de número do endereço (adicionado) -->
                <label for="addressNumber">Número do Endereço:</label><br>
                <input type="text" name="addressNumber" placeholder="123" required><br><br>
                
                <!-- Campo de telefone com DDD -->
                <label for="phone">Telefone com DDD:</label><br>
                <input type="text" name="phone" placeholder="(00) 00000-0000" required><br><br>
            </div>

            <button type="submit" name="submit_pagamento_unico">Realizar doação</button>
        </form>

        <script>
        function mostrarCamposPagamento(valor) {
            const camposCartao = document.getElementById('cartao_campos');
            camposCartao.style.display = (valor === 'CREDIT_CARD') ? 'block' : 'none';
            
            // Atualiza os campos required com base na seleção
            const camposCartaoInputs = camposCartao.querySelectorAll('input');
            camposCartaoInputs.forEach(input => {
                input.required = (valor === 'CREDIT_CARD');
            });
        }
        
        // Executa a função quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            mostrarCamposPagamento(document.getElementById('forma_pagamento').value);
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
