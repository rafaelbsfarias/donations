/**
 * Funções para processamento AJAX e exibição de mensagens em formulários de doação
 */

(function() {
    'use strict';
    
    /**
     * Acessores para dependências externas
     * Centraliza todos os acessos a objetos globais em um único lugar
     */
    const deps = {
        // Retorna o objeto ajax_object ou um objeto vazio com URL vazia
        getAjax: function() {
            return window.ajax_object || { ajax_url: '' };
        },
        
        // Acessores para AsaasFormUI
        ui: {
            setButtonState: function(button, isLoading, text) {
                if (window.AsaasFormUI && typeof AsaasFormUI.setSubmitButtonState === 'function') {
                    return AsaasFormUI.setSubmitButtonState(button, isLoading, text);
                }
                return false;
            },
            
            displaySuccess: function(form, data, donationType) {
                if (window.AsaasFormUI && typeof AsaasFormUI.displaySuccess === 'function') {
                    return AsaasFormUI.displaySuccess(form, data, donationType);
                }
                return false;
            },
            
            displayMessage: function(form, message, type) {
                if (window.AsaasFormUI && typeof AsaasFormUI.displayMessage === 'function') {
                    return AsaasFormUI.displayMessage(form, message, type);
                }
                return false;
            }
        },
        
        // Acessores para AsaasFormUtils
        utils: {
            clearMessages: function(form) {
                if (window.AsaasFormUtils && typeof AsaasFormUtils.clearMessages === 'function') {
                    return AsaasFormUtils.clearMessages(form);
                }
                return false;
            },
            
            extractErrorMessage: function(response) {
                if (window.AsaasFormUtils && typeof AsaasFormUtils.extractErrorMessage === 'function') {
                    return AsaasFormUtils.extractErrorMessage(response);
                }
                return 'Ocorreu um erro ao processar sua doação.';
            }
        }
    };
    
    /**
     * Processa o envio do formulário de doação via AJAX
     * 
     * @param {HTMLFormElement} form Formulário a ser processado
     * @param {string} loadingMessage Mensagem de carregamento
     */
    function processDonationForm(form, loadingMessage) {
        // Botão de envio
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton ? submitButton.textContent : 'Enviar';
        
        // Controlar estado do botão e limpar mensagens
        setButtonLoading(submitButton, true, loadingMessage || "Processando doação...");
        clearFormMessages(form);
        
        // Prosseguir diretamente com o envio AJAX (reCAPTCHA já foi validado)
        submitFormWithAjax(form, submitButton, originalButtonText);
    }
    
    /**
     * Envia o formulário com AJAX após validação do reCAPTCHA
     */
    function submitFormWithAjax(form, submitButton, originalButtonText, loadingMessage) {
        // Código original do envio AJAX
        setButtonLoading(submitButton, true, loadingMessage);
        
        // Obter todos os campos do formulário
        const formData = new FormData(form);
        
        try {
            // Obter objeto ajax
            const ajaxObj = deps.getAjax();
            
            // Verificar se ajax_url existe
            if (!ajaxObj.ajax_url) {
                throw new Error('ajax_url não está definido');
            }
            
            // Enviar para o backend via AJAX
            fetch(ajaxObj.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(response => {
                // Restaurar o botão
                setButtonLoading(submitButton, false, originalButtonText);
                
                if (response.success) {
                    // Processar resposta de sucesso
                    const donationType = form.getAttribute('data-donation-type') || 
                                        (form.id === 'recurring-donation-form' ? 'recurring' : 'single');
                    showDonationSuccess(form, response.data, donationType);
                } else {
                    // Processar resposta de erro
                    const errorMessage = deps.utils.extractErrorMessage(response);
                    showMessage(form, errorMessage, 'error');
                }
            })
            .catch(error => {
                // Restaurar o botão e mostrar erro
                setButtonLoading(submitButton, false, originalButtonText);
                showMessage(form, 'Erro de conexão. Por favor, tente novamente.', 'error');
                console.error('Erro na requisição AJAX:', error);
            });
        } catch (error) {
            // Restaurar o botão e mostrar erro
            setButtonLoading(submitButton, false, originalButtonText);
            showMessage(form, 'Erro ao processar o formulário. Por favor, tente novamente.', 'error');
            console.error('Erro ao configurar requisição:', error);
        }
    }
    
    /**
     * Altera o estado de carregamento do botão
     * 
     * @param {HTMLButtonElement} button O botão de envio
     * @param {boolean} isLoading Se está carregando
     * @param {string} text Texto a ser exibido
     */
    function setButtonLoading(button, isLoading, text) {
        // Tenta usar a função da dependência
        if (deps.ui.setButtonState(button, isLoading, text)) {
            return; // Se a função existir e for executada, retorna
        }
        
        // Fallback interno
        if (button) {
            button.textContent = text;
            button.disabled = isLoading;
        }
    }
    
    /**
     * Limpa mensagens do formulário
     * 
     * @param {HTMLFormElement} form O formulário
     */
    function clearFormMessages(form) {
        deps.utils.clearMessages(form);
    }
    
    /**
     * Exibe a mensagem de sucesso formatada após uma doação
     * 
     * @param {HTMLFormElement} form Formulário que foi enviado
     * @param {Object} data Os dados da doação
     * @param {string} donationType Tipo de doação ('recurring' ou 'single')
     */
    function showDonationSuccess(form, data, donationType) {
        // Tenta usar a função da dependência
        if (deps.ui.displaySuccess(form, data, donationType)) {
            return; // Se a função existir e for executada, retorna
        }
        
        // Fallback interno
        console.error('AsaasFormUI.displaySuccess não está disponível');
        const successMessage = document.createElement('div');
        successMessage.className = 'asaas-message asaas-message-success';
        //successMessage.innerHTML = '<h3>Doação realizada com sucesso!</h3><p>Obrigado por sua contribuição.</p>';
        
        form.style.display = 'none';
        form.parentNode.insertBefore(successMessage, form);
    }
    
    /**
     * Exibe uma mensagem no formulário
     * 
     * @param {HTMLFormElement} form Formulário onde a mensagem será exibida
     * @param {string} message Mensagem a ser exibida
     * @param {string} type Tipo da mensagem ('success' ou 'error')
     */
    function showMessage(form, message, type) {
        // Tenta usar a função da dependência
        if (deps.ui.displayMessage(form, message, type)) {
            return; // Se a função existir e for executada, retorna
        }
        
        // Fallback interno
        console.error('AsaasFormUI.displayMessage não está disponível');
        alert(message);
    }
    
    // Expor funções públicas - mantém a API original
    window.AsaasFormAjax = {
        processDonationForm,
        showMessage,
        showDonationSuccess
    };
    
    /**
     * Factory function para criar uma instância com dependências customizadas
     * Esta função NÃO altera o comportamento padrão, apenas adiciona uma opção
     * para criar instâncias personalizadas
     */
    window.createAsaasFormAjax = function(customDeps) {
        // Criar uma cópia das dependências padrão
        const newDeps = {
            getAjax: function() {
                return customDeps && customDeps.ajax ? customDeps.ajax : deps.getAjax();
            },
            ui: {
                setButtonState: function(button, isLoading, text) {
                    if (customDeps && customDeps.ui && typeof customDeps.ui.setSubmitButtonState === 'function') {
                        return customDeps.ui.setSubmitButtonState(button, isLoading, text);
                    }
                    return deps.ui.setButtonState(button, isLoading, text);
                },
                displaySuccess: function(form, data, donationType) {
                    if (customDeps && customDeps.ui && typeof customDeps.ui.displaySuccess === 'function') {
                        return customDeps.ui.displaySuccess(form, data, donationType);
                    }
                    return deps.ui.displaySuccess(form, data, donationType);
                },
                displayMessage: function(form, message, type) {
                    if (customDeps && customDeps.ui && typeof customDeps.ui.displayMessage === 'function') {
                        return customDeps.ui.displayMessage(form, message, type);
                    }
                    return deps.ui.displayMessage(form, message, type);
                }
            },
            utils: {
                clearMessages: function(form) {
                    if (customDeps && customDeps.utils && typeof customDeps.utils.clearMessages === 'function') {
                        return customDeps.utils.clearMessages(form);
                    }
                    return deps.utils.clearMessages(form);
                },
                extractErrorMessage: function(response) {
                    if (customDeps && customDeps.utils && typeof customDeps.utils.extractErrorMessage === 'function') {
                        return customDeps.utils.extractErrorMessage(response);
                    }
                    return deps.utils.extractErrorMessage(response);
                }
            }
        };
        
        // Retornar as funções com acesso ao novo objeto de dependências
        return {
            processDonationForm: function(form, loadingMessage) {
                // Esta implementação apenas redireciona para a função original,
                // mas em uma versão futura poderia usar as dependências personalizadas
                return processDonationForm(form, loadingMessage);
            },
            showMessage: function(form, message, type) {
                return showMessage(form, message, type);
            },
            showDonationSuccess: function(form, data, donationType) {
                return showDonationSuccess(form, data, donationType);
            }
        };
    };
})();