document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os formulários de doação
    if (window.AsaasFormUtils && AsaasFormUtils.initDonationForms) {
        AsaasFormUtils.initDonationForms();
    }

    // Aplica máscara monetária aos campos de valor
    if (window.AsaasFormMasks && AsaasFormMasks.applyMoneyMask) {
        AsaasFormMasks.applyMoneyMask();
    }

    // Configura os toggles de campos de cartão de crédito
    if (window.AsaasFormUI && AsaasFormUI.setupPaymentMethodToggles) {
        AsaasFormUI.setupPaymentMethodToggles();
    }
});