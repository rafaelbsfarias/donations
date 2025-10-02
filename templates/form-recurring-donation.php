<div class="asaas-donation-form">
    
    <h2>Doação Mensal</h2>
    <br>
    <p>Preencha as informações abaixo para cadastrar uma doação mensal. Fique tranquilo, essa modalidade de doação não ocupa o limite do seu cartão de credito.</p>

    <form class="recurring-donation-form" data-donation-type="recurring">
        <input type="hidden" name="action" value="process_donation">
        <input type="hidden" name="donation_type" value="<?php echo esc_attr($form_data['form_type']); ?>">
        
        <?php Asaas_Nonce_Manager::generate_nonce_field($form_data['nonce_action']); ?>
        
        <label for="full-name">Nome Completo:</label>
        <input type="text" id="full-name" name="full_name" maxlength="50" placeholder="Coloque seu nome aqui" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" maxlength="64" placeholder="Coloque seu e-mail aqui" required>

        <label for="cpf-cnpj">CPF ou CNPJ:</label>
        <input type="text" class="cpf-cnpj" name="cpf_cnpj" maxlength="14" placeholder="Somente números" required>

        <label for="donation-value">Valor da Doação:</label>
        <input type="text" id="donation-value" name="donation_value" placeholder="Ex.: 50,00" required>

        <input type="hidden" name="payment_method" value="card">
        
        <div class="card-fields">
            <label for="card-number">Número do Cartão:</label>
            <input type="text" id="card-number" name="card_number" maxlength="19" placeholder="Somente números" required>

            <label for="expiry-month">Mês de Validade:</label>
            <input type="text" id="expiry-month" name="expiry_month" maxlength="2" placeholder="MM" required>

            <label for="expiry-year">Ano de Validade:</label>
            <input type="text" id="expiry-year" name="expiry_year" maxlength="4" placeholder="AAAA" required>

            <label for="ccv">CCV:</label>
            <input type="text" id="ccv" name="ccv" maxlength="3" placeholder="Ex.: 123" required>
            
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" maxlength="8" placeholder="Somente números" required>
            
            <label for="address-number">Número do endereço:</label>
            <input type="text" id="address-number" name="address_number" placeholder="Ex.: 123" required>
            
            <label for="phone">Telefone:</label>
            <input type="text" id="phone" name="phone" placeholder="DDD + número" required>
        </div>

        <button type="submit" id="submit-recurring-donation">Realizar Doação</button>
    </form>
</div>