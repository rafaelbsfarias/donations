# Análise Inicial: Erro de Segurança em Produção

## 📋 Contexto do Problema

### Relato do Usuário
- Em produção: erro "Erro de segurança. Recarregue a página e tente novamente."
- Ambiente de teste (sem reCAPTCHA): funciona normalmente
- Produção (com reCAPTCHA): apresenta erro + imagem de erro

### Evidências Observadas
- Erro ocorre na linha 71 do `ajax-handler.php`
- Problema relacionado à configuração do reCAPTCHA
- Funciona sem reCAPTCHA, falha com reCAPTCHA

## 🔍 Análise Técnica Detalhada

### 1. Fluxo de Execução Atual

#### Frontend (`form-recaptcha.js`)
```javascript
form.addEventListener('submit', function(event) {
    event.preventDefault();
    // Tenta gerar token reCAPTCHA
    generateRecaptchaToken(function(token) {
        if (token) {
            // Sucesso: adiciona token e processa
            AsaasFormAjax.processDonationForm(form);
        } else {
            // Falha: mostra alert
            alert('Erro na verificação de segurança. Tente novamente.');
        }
    });
});
```

#### Backend (`ajax-handler.php` - Linha 71)
```php
if (!isset($_POST[$nonce_field_name]) || !Asaas_Nonce_Manager::verify_nonce($_POST, $donation_type_action)) {
    wp_send_json_error([
        'message' => __('Erro de segurança. Recarregue a página e tente novamente.', 'asaas-easy-subscription-plugin')
    ]);
}
```

### 2. Sequência de Validações

1. **Nonce verificado primeiro** (linha 71)
2. **reCAPTCHA verificado depois** (linha 85+)
3. **Outras validações** (anti-bot, etc.)

### 3. Diferenças Entre Ambientes

#### Ambiente de Teste (Funcionando)
- **Sem chaves reCAPTCHA** configuradas
- `form-recaptcha.js` não intercepta submit
- Form enviado diretamente via AJAX padrão
- Nonce validado com sucesso

#### Produção (Falha)
- **Com chaves reCAPTCHA** configuradas
- `form-recaptcha.js` intercepta submit
- reCAPTCHA pode falhar ou ter timing issues
- Form enviado com estado potencialmente inconsistente
- Nonce falha na validação

## 🎯 Hipóteses Principais

### Hipótese 1: Falha Silenciosa do reCAPTCHA
**Cenário**: reCAPTCHA falha, mas form continua sendo enviado sem tratamento adequado.

**Fluxo problemático**:
1. User clica submit
2. `form-recaptcha.js` intercepta
3. reCAPTCHA falha (timeout, erro, etc.)
4. Callback retorna `null`
5. Alert mostrado, botão reabilitado
6. **Mas form pode ser enviado novamente** ou estado fica inconsistente
7. Backend recebe request com nonce potencialmente invalidado

### Hipótese 2: Ordem de Carregamento de Scripts
**Cenário**: Scripts carregam fora de ordem, causando múltiplas interceptações.

**Problema identificado**:
```php
// enqueue-scripts.php
wp_register_script('asaas-form-recaptcha', ..., ['jquery'], ...);
wp_register_script('asaas-form-ajax', ..., ['jquery', 'asaas-form-utils', 'asaas-form-ui'], ...);
```

O `form-recaptcha` não depende do `form-ajax`, mas ambos podem interceptar o mesmo form.

### Hipótese 3: Estado do Formulário Corrompido
**Cenário**: Múltiplas tentativas ou estados inconsistentes.

**Possíveis causas**:
- Token reCAPTCHA adicionado/removido incorretamente
- Múltiplas submissões rápidas
- Estado do botão não controlado adequadamente

## 🔧 Evidências no Código

### 1. Controle de Estado Insuficiente
```javascript
// form-recaptcha.js
if (submitButton && submitButton.disabled) {
    return; // Previne múltiplas submissões
}
```
Este controle existe, mas pode não ser suficiente.

### 2. Tratamento de Erro do reCAPTCHA
```javascript
generateRecaptchaToken(function(token) {
    if (token) {
        // Sucesso
    } else {
        // Falha - apenas alert
        alert('Erro na verificação de segurança. Tente novamente.');
    }
});
```
Quando falha, apenas mostra alert, mas não garante que o form não será enviado.

### 3. Nonce Verification
```php
$donation_type_action = isset($_POST['donation_type']) && sanitize_text_field(wp_unslash($_POST['donation_type'])) === 'recurring'
    ? Asaas_Nonce_Manager::ACTION_RECURRING_DONATION
    : Asaas_Nonce_Manager::ACTION_SINGLE_DONATION;
```
O nonce é verificado baseado no `donation_type`, que pode estar inconsistente.

## 📊 Possíveis Soluções

### Solução 1: Unificar Interceptação
- Um único script controla todo o fluxo
- Eliminar duplicação de interceptação
- Melhor controle de estado

### Solução 2: Implementar Fallback Adequado
- Quando reCAPTCHA falha, permitir continuação sem ele (com logs)
- Ou bloquear completamente até reCAPTCHA funcionar
- Ou tentar novamente automaticamente

### Solução 3: Melhorar Ordem de Carregamento
- Garantir dependências corretas entre scripts
- Usar promises ou callbacks para sincronização

### Solução 4: Reordenar Validações Backend
- Verificar se faz sentido reordenar nonce vs reCAPTCHA
- Implementar validação condicional

## 📋 Próximos Passos

1. **Adicionar logs detalhados** no frontend
2. **Implementar modo debug** temporário
3. **Criar testes** para reproduzir o erro
4. **Analisar logs de produção** em tempo real
5. **Implementar correção** baseada na causa confirmada

## 📚 Logs e Dados Coletados

### Logs Backend (ajax-handler.php)
- Nonce verification fails
- reCAPTCHA token may or may not be present
- Form data seems consistent

### Console Frontend (Provável)
- reCAPTCHA errors or timeouts
- Multiple form submissions
- Script loading issues

---

**Data da análise**: Outubro 2025
**Analista**: Rafael
**Status**: ✅ Análise completa - Aguardando implementação</content>
<parameter name="filePath">/home/rafael/workspace/asaas-easy-subscription-plugin/docs/correcao-erro-seguranca/analise-inicial.md