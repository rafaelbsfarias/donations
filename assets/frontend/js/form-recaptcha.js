/**
 * Funções para integração com reCAPTCHA
 */

/**
 * Funções para integração com reCAPTCHA
 */

(function() {
    'use strict';

    // Estado global do formulário
    let formState = {
        isSubmitting: false,
        recaptchaReady: false,
        submitAttempts: 0
    };

    /**
     * Inicializa a lógica do reCAPTCHA
     */
    function initializeRecaptchaLogic() {
        console.log('ASAAS: Inicializando lógica reCAPTCHA');

        document.querySelectorAll('form.single-donation-form, form.recurring-donation-form').forEach(function(form) {
            if (form.dataset.submitListenerAttached === 'true') {
                return;
            }
            form.dataset.submitListenerAttached = 'true';

            form.addEventListener('submit', handleFormSubmit);
        });
    }

    /**
     * Manipula o envio do formulário de forma unificada
     */
    function handleFormSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');

        // Verificar estado
        if (formState.isSubmitting) {
            console.log('ASAAS: Submissão já em progresso');
            return;
        }

        // Marcar como submitting
        formState.isSubmitting = true;
        formState.submitAttempts++;

        updateButtonState(submitButton, true, 'Verificando segurança...');

        // Verificar se reCAPTCHA está configurado
        if (asaasRecaptcha && asaasRecaptcha.siteKey) {
            console.log('ASAAS: reCAPTCHA configurado, executando validação');
            handleRecaptchaFlow(form, submitButton);
        } else {
            console.log('ASAAS: reCAPTCHA não configurado, enviando diretamente');
            // Fallback: enviar sem reCAPTCHA
            submitFormDirectly(form, submitButton);
        }
    }

    /**
     * Gerencia o fluxo com reCAPTCHA
     */
    function handleRecaptchaFlow(form, submitButton) {
        generateRecaptchaToken()
            .then(token => {
                if (token) {
                    console.log('ASAAS: Token reCAPTCHA gerado com sucesso');
                    addRecaptchaTokenToForm(form, token);
                    submitFormDirectly(form, submitButton);
                } else {
                    // Fallback: continuar sem reCAPTCHA mas logar
                    console.warn('ASAAS: Falha no reCAPTCHA, continuando sem token');
                    logRecaptchaFailure(form);
                    submitFormDirectly(form, submitButton);
                }
            })
            .catch(error => {
                console.error('ASAAS: Erro no reCAPTCHA:', error);
                logRecaptchaFailure(form);
                submitFormDirectly(form, submitButton);
            });
    }

    /**
     * Gera token do reCAPTCHA com promise
     */
    function generateRecaptchaToken() {
        return new Promise((resolve, reject) => {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.ready) {
                grecaptcha.ready(function() {
                    var siteKey = asaasRecaptcha.siteKey;
                    if (!siteKey) {
                        console.error('ASAAS: Chave do reCAPTCHA não configurada');
                        resolve(null);
                        return;
                    }

                    grecaptcha.execute(siteKey, {action: 'asaas_donation'})
                    .then(function(token) {
                        resolve(token);
                    }).catch(function(error) {
                        console.error('ASAAS: Erro ao executar grecaptcha.execute:', error);
                        resolve(null);
                    });
                });
            } else {
                console.error('ASAAS: grecaptcha não está disponível');
                resolve(null);
            }
        });
    }

    /**
     * Registra falha do reCAPTCHA para análise
     */
    function logRecaptchaFailure(form) {
        // Enviar log para backend se possível
        if (typeof navigator !== 'undefined' && navigator.sendBeacon) {
            const data = new FormData();
            data.append('action', 'log_recaptcha_failure');
            data.append('form_type', form.classList.contains('single-donation-form') ? 'single' : 'recurring');
            data.append('user_agent', navigator.userAgent);
            data.append('timestamp', Date.now());

            navigator.sendBeacon(ajax_object.ajax_url, data);
        }
    }

    /**
     * Envia o formulário via AJAX
     */
    function submitFormDirectly(form, submitButton) {
        if (typeof AsaasFormAjax !== 'undefined' && typeof AsaasFormAjax.processDonationForm === 'function') {
            AsaasFormAjax.processDonationForm(form, 'Finalizando doação...');
        } else {
            console.error('ASAAS: AsaasFormAjax não disponível');
            resetFormState(submitButton, 'Erro interno. Tente novamente.');
        }
    }

    /**
     * Atualiza estado do botão
     */
    function updateButtonState(button, isLoading, text) {
        if (button) {
            button.textContent = text;
            button.disabled = isLoading;
        }
    }

    /**
     * Reseta estado do formulário
     */
    function resetFormState(submitButton, message) {
        formState.isSubmitting = false;
        updateButtonState(submitButton, false, message || 'Realizar Doação');
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
            console.warn('ASAAS: reCAPTCHA não configurado - pulando inicialização');
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
                console.error('ASAAS: Timeout aguardando reCAPTCHA');
            }, 10000);
        }
    });

    // Expor funções para debug
    window.AsaasRecaptchaDebug = {
        getFormState: () => formState,
        resetFormState: resetFormState,
        initializeRecaptchaLogic: initializeRecaptchaLogic
    };
})();