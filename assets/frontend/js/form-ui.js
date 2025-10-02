/**
 * Funções para manipulação da interface do usuário e exibição de campos
 */

(function() {
    'use strict';
    
    // Garantir que o objeto AsaasFormUI existe
    window.AsaasFormUI = window.AsaasFormUI || {};
    
    /**
     * Implementação do PaymentMethodToggler
     */
    class PaymentMethodToggler {
        /**
         * @param {HTMLFormElement} formElement
         */
        constructor(formElement) {
            this.form = formElement;
            this.select = this.form.querySelector('.payment-method');
            this.cardContainer = this.form.querySelector('.card-fields');
        }
    
        init() {
            if (!this.select || !this.cardContainer) return;
            this._toggle(this.select.value);
            this.select.addEventListener('change', () => {
                this._toggle(this.select.value);
            });
        }
    
        /**
         * @param {string} paymentMethod
         * @private
         */
        _toggle(paymentMethod) {
            const show = paymentMethod === 'card';
            this.cardContainer.style.display = show ? '' : 'none';
            this.cardContainer
                .querySelectorAll('input')
                .forEach(input => {
                    if (show) input.setAttribute('required', '');
                    else     input.removeAttribute('required');
                });
        }
    }
    
    /**
     * Implementação do controller de UI
     */
    class FormUIController {
        static init() {
            // selecione ambos single e recurring (ajuste os seletores ao seu HTML)
            const forms = Array.from(document.querySelectorAll('.single-donation-form, .recurring-donation-form'));
            forms.forEach(form => new PaymentMethodToggler(form).init());
        }
    }
    
    /**
     * Exibe uma mensagem no formulário
     * 
     * @param {HTMLFormElement} form Formulário onde a mensagem será exibida
     * @param {string} message Mensagem a ser exibida
     * @param {string} type Tipo da mensagem ('success' ou 'error')
     */
    function displayMessage(form, message, type) {
        // Limpar mensagens anteriores
        if (window.AsaasFormUtils && AsaasFormUtils.clearMessages) {
            AsaasFormUtils.clearMessages(form);
        }
        
        // Criar o elemento de mensagem
        const messageDiv = document.createElement('div');
        messageDiv.className = `asaas-message asaas-message-${type}`;
        messageDiv.innerHTML = message;
        
        // Adicionar ID único para a mensagem de erro (para referência de rolagem)
        if (type === 'error') {
            messageDiv.id = 'asaas-error-message';
        }
        
        // Inserir a mensagem antes do formulário
        form.parentNode.insertBefore(messageDiv, form);
        
        // Se for mensagem de erro, rolar até ela
        if (type === 'error') {
            setTimeout(() => {
                messageDiv.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 100);
        }
    }
    
    /**
     * Exibe a mensagem de sucesso formatada após uma doação
     * 
     * @param {HTMLFormElement} form Formulário que foi enviado
     * @param {Object} data Os dados da doação
     * @param {string} donationType Tipo de doação ('recurring' ou 'single')
     */
    function displaySuccess(form, data, donationType) {
        // Ocultar elementos do formulário
        form.style.display = 'none';
        
        // Limpar mensagens anteriores
        if (window.AsaasFormUtils && AsaasFormUtils.clearMessages) {
            AsaasFormUtils.clearMessages(form);
        }
        
        // Ocultar também o título h2 e o parágrafo introdutório que estão fora do formulário
        const formContainer = form.closest('.asaas-donation-form');
        if (formContainer) {
            Array.from(formContainer.children).forEach(child => {
                if (child !== form && (child.tagName === 'H2' || child.tagName === 'P')) {
                    child.style.display = 'none';
                }
            });
        }
        
        // Verificar se os dados estão aninhados (comum em respostas de API)
        const responseData = data.data || data;
        
        // Obter dados da resposta
        let value = AsaasFormUtils.getDataValue(responseData, ['value', 'donation_value']);
        let formattedValue = AsaasFormUtils.formatCurrencyValue(value);
        let paymentMethod = responseData.payment_method || '';
        
        // Cria o elemento de mensagem de sucesso
        const successDiv = document.createElement('div');
        successDiv.className = 'donation-success-container';
        
        // Verifica se é um pagamento via boleto
        if (donationType === 'single' && paymentMethod === 'boleto') {
            // Dados específicos do boleto
            let dueDate = AsaasFormUtils.getDataValue(responseData, ['due_date', 'dueDate']);
            dueDate = AsaasFormUtils.formatDate(dueDate);
            
            let bankSlipUrl = responseData.bank_slip_url || '';
            let invoiceUrl = responseData.invoice_url || '';
            let boletoNumber = AsaasFormUtils.getDataValue(responseData, ['nossoNumero'], '');
            
            // HTML para página de sucesso do boleto
            let html = `
                <div class="boleto-success">
                    <h2 class="success-title">Boleto para doação gerado!</h2>
                    <div class="boleto-info">
                        <div class="info-row">
                            <span class="info-label">Valor:</span>
                            <span class="info-value">R$ ${formattedValue}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Vencimento:</span>
                            <span class="info-value">${dueDate}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Número do Boleto:</span>
                            <span class="info-value">${boletoNumber}</span>
                        </div>
                    </div>
                    <div class="boleto-actions">
                        <a href="${bankSlipUrl}" target="_blank" class="btn btn-download">Baixar Boleto</a>
                        <a href="${invoiceUrl}" target="_blank" class="btn btn-invoice">Visualizar Fatura</a>
                    </div>
                    <button class="btn btn-close" onclick="AsaasFormUtils.hideDonationSuccess(this)">Fechar</button>
                </div>
            `;
            
            successDiv.innerHTML = html;
        } else if (donationType === 'single' && paymentMethod === 'pix') {
            // Dados específicos do PIX
            let pixCode = responseData.pix_code || '';
            let pixText = responseData.pix_text || '';
            
            // HTML para página de sucesso do PIX - layout atualizado
            let html = `
                <div class="pix-success">
                    <h2 class="success-title">Só mais um passo!</h2>
                    <br>
                    <p>Leia o QR Code ou copie o código abaixo para realizar sua doação</p>
                    <div class="pix-info">
                        <div class="info-row">
                            <span class="info-label">Valor:</span>
                            <span class="info-value">R$ ${formattedValue}</span>
                        </div>
                    </div>
                    
                    <div class="pix-qrcode">
                        <img src="data:image/png;base64,${pixCode}" alt="QR Code PIX">
                    </div>
                    
                    <textarea id="pix-code-text" class="pix-code-text" readonly>${pixText}</textarea>
                    
                    <button class="btn btn-copy-wide" onclick="AsaasFormUtils.copyPixCode()">Copiar</button>
                </div>
            `;
            
            successDiv.innerHTML = html;
        } else if (donationType === 'recurring') {
            // Código para doação recorrente
            let nextDueDate = AsaasFormUtils.getDataValue(responseData, ['nextDueDate', 'next_due_date']);
            let status = AsaasFormUtils.getDataValue(responseData, ['status', 'subscription_status']);
            
            // Traduzir o status se for "ACTIVE"
            if (status && status.toUpperCase() === 'ACTIVE') {
                status = 'Ativa';
            }
            
            // Formatar data para padrão brasileiro
            nextDueDate = AsaasFormUtils.formatDate(nextDueDate);
            
            let html = `
                <h2>Doação mensal cadastrada com sucesso!</h2>
                <div class="donation-info">
                    <p><strong>Valor mensal:</strong> R$ ${formattedValue}</p>
                    <p><strong>Próxima cobrança:</strong> ${nextDueDate}</p>
                    <p><strong>Situação:</strong> ${status}</p>
                </div>
                <div class="thank-you-message">
                    <h3>Obrigado por sua generosidade!</h3>
                    <p>Sua contribuição é muito importante para nossa causa.</p>
                    <p>A doação será cobrada automaticamente todos os meses, sem ocupar seu limite no cartão de crédito.</p>
                </div>
            `;
            
            successDiv.innerHTML = html;
        } else {
            // Código para outros tipos de doação única
            let paymentStatus = AsaasFormUtils.getDataValue(responseData, ['payment_status', 'status']);
            let bankSlipUrl = responseData.bank_slip_url || null;
            let invoiceUrl = responseData.invoice_url || null;
            
            // Traduzir status comuns do inglês
            if (paymentStatus && paymentStatus.toUpperCase() === 'CONFIRMED') {
                paymentStatus = 'Confirmado';
            } else if (paymentStatus && paymentStatus.toUpperCase() === 'PENDING') {
                paymentStatus = 'Pendente';
            }
            
            let html = `
                <h2>Doação realizada com sucesso!</h2>
                <div class="donation-info">
                    <p><strong>Valor doado:</strong> R$ ${formattedValue}</p>
                    <p><strong>Situação:</strong> ${paymentStatus}</p>
                </div>`;
            
            // Adicionar link do boleto se disponível
            if (bankSlipUrl) {
                html += `
                    <div class="payment-actions">
                        <p>Clique no botão abaixo para visualizar e imprimir o boleto:</p>
                        <a href="${bankSlipUrl}" target="_blank" class="button button-primary">Visualizar Boleto</a>
                    </div>`;
            }
            
            // Adicionar link da fatura se disponível
            if (invoiceUrl) {
                html += `
                    <div class="invoice-link">
                        <p>Você também pode <a href="${invoiceUrl}" target="_blank">acessar a fatura online</a>.</p>
                    </div>`;
            }
            
            html += `
                <div class="thank-you-message">
                    <h3>Obrigado por sua generosidade!</h3>
                    <p>Sua contribuição é muito importante para nossa causa.</p>
                </div>
            `;
            
            successDiv.innerHTML = html;
        }
        
        // Inserir a mensagem de sucesso antes do formulário
        form.parentNode.insertBefore(successDiv, form);
    }
    
    /**
     * Define o estado do botão de submit (habilitado/desabilitado e texto)
     * 
     * @param {HTMLButtonElement} button O elemento do botão
     * @param {boolean} isLoading Se está carregando ou não
     * @param {string} text Texto a ser exibido
     */
    function setSubmitButtonState(button, isLoading, text) {
        if (button) {
            button.disabled = isLoading;
            button.textContent = text;
            
            // Adicionar/remover classe de carregamento se desejar estilização adicional
            if (isLoading) {
                button.classList.add('loading');
            } else {
                button.classList.remove('loading');
            }
        }
    }
    
    // Estender o objeto AsaasFormUI com todas as funções (em vez de sobrescrevê-lo)
    Object.assign(window.AsaasFormUI, {
        setupPaymentMethodToggles: FormUIController.init,
        displayMessage: displayMessage,
        displaySuccess: displaySuccess,
        setSubmitButtonState: setSubmitButtonState
    });
})();