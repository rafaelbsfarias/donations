<?php
require_once __DIR__ . '/../settings.php';

class FormularioPagamento {
    public function render() {
        ob_start();
        ?>
        <form method="POST" action="">
            <?php wp_nonce_field('criar_pagamento_unico', 'pagamento_unico_nonce'); ?>

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

            <input type="submit" name="submit_pagamento_unico" value="Pagar">
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
