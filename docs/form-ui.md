# Documentação: assets/frontend/js/form-ui.js

## Visão Geral
Gerencia a interface do usuário dos formulários, incluindo estados de botão, mensagens e exibição de sucesso.

## Funcionalidades
- `setSubmitButtonState`: Altera estado do botão (loading, text).
- `displayMessage`: Mostra mensagens de erro/sucesso.
- `displaySuccess`: Exibe resultado de doação (PIX, cartão, etc.).
- `setupPaymentMethodToggles`: Alterna campos por método de pagamento.

## Dependências
- `form-utils.js` para utilitários.

## Considerações
- Interações dinâmicas com DOM.