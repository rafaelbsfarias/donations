/**
 * Funções para integração com reCAPTCHA
 */

(function() {
    'use strict';

    /**
     * Inicializa a lógica do reCAPTCHA
     */
    function initializeRecaptchaLogic() {
        // console.log('reCAPTCHA API pronta, inicializando lógica.');

        document.querySelectorAll('form.single-donation-form, form.recurring-donation-form').forEach(function(form) {
            if (form.dataset.submitListenerAttached === 'true') {
                return;
            }
            form.dataset.submitListenerAttached = 'true';

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                var submitButton = form.querySelector('button[type="submit"]');
                if (submitButton && submitButton.disabled) {
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Verificando segurança...';
                }

                // Gerar token reCAPTCHA antes de processar
                generateRecaptchaToken(function(token) {
                    if (token) {
                        // Adicionar token ao formulário
                        addRecaptchaTokenToForm(form, token);
                        
                        // Processar formulário via AJAX
                        if (typeof AsaasFormAjax !== 'undefined' && typeof AsaasFormAjax.processDonationForm === 'function') {
                            AsaasFormAjax.processDonationForm(form, 'Processando doação...');
                        } else {
                            alert('Erro: Sistema de processamento não encontrado.');
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = 'Realizar Doação';
                            }
                        }
                    } else {
                        alert('Erro na verificação de segurança. Tente novamente.');
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Realizar Doação';
                        }
                    }
                });
            });
        });
    }

    /**
     * Gera token do reCAPTCHA
     */
    function generateRecaptchaToken(callback) {
        if (typeof grecaptcha !== 'undefined' && grecaptcha.ready) {
            grecaptcha.ready(function() {
                var siteKey = asaasRecaptcha.siteKey;
                if (!siteKey) {
                    console.error('Chave do reCAPTCHA não configurada');
                    callback(null);
                    return;
                }
                
                grecaptcha.execute(siteKey, {action: 'asaas_donation'})
                .then(function(token) {
                    callback(token);
                }).catch(function(error) {
                    console.error('Erro ao gerar token reCAPTCHA:', error);
                    callback(null);
                });
            });
        } else {
            console.error('reCAPTCHA não está disponível');
            callback(null);
        }
    }

    /**
     * Adiciona o token do reCAPTCHA ao formulário
     */
    function addRecaptchaTokenToForm(form, token) {
        let tokenInput = form.querySelector('input[name="g-recaptcha-response"]');
        
        if (!tokenInput) {
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'g-recaptcha-response';
            form.appendChild(tokenInput);
        }
        
        tokenInput.value = token;
    }

    // Inicializar quando DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof asaasRecaptcha === 'undefined' || !asaasRecaptcha.siteKey) {
            console.warn('reCAPTCHA não configurado - pulando inicialização');
            return;
        }

        // Verificar se o script do reCAPTCHA já foi carregado pelo WordPress
        if (typeof grecaptcha !== 'undefined') {
            initializeRecaptchaLogic();
        } else {
            // Aguardar carregamento do reCAPTCHA
            var checkInterval = setInterval(function() {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.ready) {
                    clearInterval(checkInterval);
                    initializeRecaptchaLogic();
                }
            }, 100);
            
            // Timeout após 10 segundos
            setTimeout(function() {
                clearInterval(checkInterval);
                console.error('Timeout aguardando reCAPTCHA');
            }, 10000);
        }
    });
})();