/**
 * Funções utilitárias para manipulação de formulários
 */

(function() {
    'use strict';
    
    /**
     * Inicializa os formulários de doação
     */
    function initDonationForms() {
        // Todos os formulários de doação única
        const singleForms = document.querySelectorAll('.single-donation-form');
        singleForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                AsaasFormAjax.processDonationForm(form, 'Processando doação única...');
            });
        });
        
           // Todos os formulários de doação recorrente
           const recurringForms = document.querySelectorAll('.recurring-donation-form');
           recurringForms.forEach(form => {
               form.addEventListener('submit', function(e) {
                   e.preventDefault();
                   AsaasFormAjax.processDonationForm(form, 'Processando assinatura...');
               });
           });
    }
    
    /**
     * Formata um valor para exibição como moeda
     * 
     * @param {string|number} value Valor a formatar
     * @return {string} Valor formatado (ex: 1.234,56)
     */
    function formatCurrencyValue(value) {
        try {
            // Primeiro, garantir que value seja um número
            let numValue;
            if (typeof value === 'number') {
                numValue = value;
            } else if (typeof value === 'string') {
                // Remover qualquer caractere não numérico, exceto pontos
                value = value.replace(/[^\d.,]/g, '');
                // Substituir vírgula por ponto para garantir conversão correta
                value = value.replace(',', '.');
                numValue = parseFloat(value);
            }
            
            // Verificar se é um número válido
            if (!isNaN(numValue)) {
                // Formatar com duas casas decimais e substituir ponto por vírgula
                let formattedValue = numValue.toFixed(2).replace('.', ',');
                
                // Adicionar separadores de milhar (opcional)
                const parts = formattedValue.split(',');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return parts.join(',');
            } else {
                return '0,00';
            }
        } catch (e) {
            return '0,00';
        }
    }
    
    /**
     * Limpa todas as mensagens do formulário
     * 
     * @param {HTMLFormElement} form Formulário cujas mensagens serão limpas
     */
    function clearMessages(form) {
        // Selecionar todas as mensagens anteriores no contêiner do formulário
        const messages = form.parentNode.querySelectorAll('.asaas-message');
        messages.forEach(message => message.remove());
    }
    
    /**
     * Obtém um valor de um objeto, verificando múltiplas chaves possíveis
     * 
     * @param {Object} data Objeto com os dados
     * @param {Array} keys Array de possíveis chaves a verificar
     * @param {*} defaultValue Valor padrão se nenhuma chave for encontrada
     * @return {*} Valor encontrado ou valor padrão
     */
    function getDataValue(data, keys, defaultValue = '-') {
        if (!data) return defaultValue;
        
        for (const key of keys) {
            if (data[key] !== undefined) {
                return data[key];
            }
        }
        return defaultValue;
    }
    
    /**
     * Formata uma data do formato ISO para o formato brasileiro
     * 
     * @param {string} dateStr String de data no formato YYYY-MM-DD
     * @return {string} Data formatada (DD/MM/YYYY) ou o valor original se não for formatável
     */
    function formatDate(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return dateStr;
        
        // Verificar se é no formato YYYY-MM-DD
        if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const parts = dateStr.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        
        return dateStr;
    }
    
    /**
     * Oculta a mensagem de sucesso da doação
     * 
     * @param {HTMLElement} closeButton O botão de fechar que foi clicado
     */
    function hideDonationSuccess(closeButton) {
        const container = closeButton.closest('.donation-success-container');
        if (container) {
            container.style.display = 'none';
            
            // Opcionalmente, reexibir o formulário
            const formContainer = container.closest('.asaas-donation-form');
            if (formContainer) {
                // Mostrar o formulário e outros elementos
                Array.from(formContainer.children).forEach(child => {
                    if (child !== container) {
                        child.style.display = '';
                    }
                });
                
                // Procurar o formulário e resetá-lo
                const form = formContainer.querySelector('form');
                if (form) {
                    form.reset();
                    form.style.display = '';
                }
            }
        }
    }
    
    /**
     * Copia o código PIX para a área de transferência
     */
    function copyPixCode() {
        const pixCodeInput = document.getElementById('pix-code-text');
        if (pixCodeInput) {
            pixCodeInput.select();
            
            try {
                document.execCommand('copy');
                
                // Altera o texto do botão temporariamente para indicar sucesso
                const copyButton = document.querySelector('.btn-copy-wide');
                if (copyButton) {
                    const originalText = copyButton.textContent;
                    copyButton.textContent = 'Copiado!';
                    
                    // Restaura o texto original após 2 segundos
                    setTimeout(() => {
                        copyButton.textContent = originalText;
                    }, 2000);
                }
            } catch (err) {
                alert('Não foi possível copiar automaticamente. Por favor, copie manualmente.');
            }
        }
    }
    
    /**
     * Extrai a mensagem de erro de uma resposta
     * 
     * @param {Object} response Resposta da API
     * @return {string} Mensagem de erro extraída
     */
    function extractErrorMessage(response) {
        let errorMessage = 'Ocorreu um erro ao processar sua doação.';
        
        if (response.data) {
            if (response.data.errors) {
                if (typeof response.data.errors === 'object' && !Array.isArray(response.data.errors)) {
                    errorMessage = Object.values(response.data.errors).join('<br>');
                } else if (Array.isArray(response.data.errors)) {
                    errorMessage = response.data.errors.join('<br>');
                } else {
                    errorMessage = response.data.errors;
                }
            } else if (response.data.message) {
                errorMessage = response.data.message;
            }
        }
        
        return errorMessage;
    }
    
    // Expor funções públicas
    window.AsaasFormUtils = {
        initDonationForms,
        formatCurrencyValue,
        clearMessages,
        getDataValue,
        formatDate,
        hideDonationSuccess,
        copyPixCode,
        extractErrorMessage
    };
})();