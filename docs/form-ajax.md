# Documentação: assets/frontend/js/form-ajax.js

## Visão Geral
Este arquivo JavaScript gerencia o processamento AJAX dos formulários de doação, incluindo submissão, validação reCAPTCHA e exibição de mensagens.

## Estrutura do Arquivo
- **IIFE (Immediately Invoked Function Expression)**: Escopo isolado.
- **Objeto `deps`**: Centraliza acessos a dependências globais (ajax_object, AsaasFormUI, AsaasFormUtils).
- **Funções Principais**:
  - `processDonationForm(form, loadingMessage)`: Processa submissão via AJAX.
  - `addRecaptchaTokenToForm(form, token)`: Adiciona token reCAPTCHA.
  - `submitFormWithAjax(form, submitButton, ...)`: Submete com AJAX após reCAPTCHA.

## Funcionalidades
- Integração com WordPress AJAX (wp_ajax_*).
- Suporte a reCAPTCHA v3.
- Estados de loading para botões.
- Tratamento de erros e sucesso.

## Dependências
- `form-ui.js` e `form-utils.js` para UI e utilitários.
- Objeto global `ajax_object` do WordPress.

## Notas de Segurança
- Validação client-side com reCAPTCHA.
- Sanitização de dados antes de enviar.

## Considerações para Produção
- Minificar e otimizar para performance.
- Adicionar fallbacks para JavaScript desabilitado.