# Logs de Produção - Correção Erro Segurança

## 📋 Logs Esperados para Diagnóstico

### 1. Logs do ajax-handler.php

#### Logs Atuais (WP_DEBUG=true)
```
ASAAS: Requisição AJAX recebida. HTTP Method: POST
ASAAS: Chaves POST recebidas: ["action","donation_type","asaas_nonce",...]
ASAAS: Verificando nonce - Campo esperado: asaas_nonce, Ação esperada: single_donation
ASAAS: Nonce presente no POST: Sim
ASAAS: Falha na verificação do nonce. Campo esperado: asaas_nonce, Ação esperada: single_donation
ASAAS: Dados POST (parciais) na falha do nonce: {...}
```

#### Logs Adicionais Necessários
```
ASAAS: reCAPTCHA interceptou formulário
ASAAS: Tentando gerar token reCAPTCHA
ASAAS: Token reCAPTCHA gerado com sucesso
ASAAS: Falha ao gerar token reCAPTCHA: [erro]
ASAAS: Formulário enviado após falha reCAPTCHA
ASAAS: Estado do botão: [enabled/disabled]
```

### 2. Logs do Frontend (Console)

#### Console Errors Esperados
```javascript
// Erro de reCAPTCHA
recaptcha__en.js:402 Uncaught Error: Invalid site key or not loaded in api.js

// Erro de script
GET https://.../form-recaptcha.js net::ERR_ABORTED 404

// Múltiplas submissões
ASAAS Debug: Submissão já em progresso, ignorando clique
```

#### Console Logs de Debug
```javascript
ASAAS: Formulário interceptado
ASAAS: Executando grecaptcha.execute
ASAAS: Token gerado: [token]
ASAAS: Chamando processDonationForm
```

## 🔍 Padrões a Identificar

### Padrão 1: Falha de reCAPTCHA → Nonce Fail
```
[Frontend] reCAPTCHA falha
[Frontend] Form continua sendo enviado
[Backend] Nonce verification fails
[Result] Erro "Erro de segurança"
```

### Padrão 2: Timing Issues
```
[Frontend] Form interceptado
[Frontend] reCAPTCHA ainda carregando
[Frontend] Timeout aguardando reCAPTCHA
[Backend] Request sem token reCAPTCHA
```

### Padrão 3: Múltiplas Interceptações
```
[Frontend] form-recaptcha.js intercepta
[Frontend] form-ajax.js também intercepta
[Frontend] Conflito de event listeners
[Backend] Estado inconsistente
```

## 📊 Queries para Análise de Logs

### Buscar por padrões específicos
```bash
# Falhas de nonce
grep "Falha na verificação do nonce" /var/log/apache2/error.log

# Tentativas reCAPTCHA
grep "ASAAS.*reCAPTCHA" /var/log/apache2/error.log

# Erros de script
grep "form-recaptcha.js" /var/log/apache2/error.log
```

### Análise de frequência
```bash
# Contar erros por hora
grep "Erro de segurança" /var/log/apache2/error.log | cut -d' ' -f1,2 | sort | uniq -c

# IPs com mais erros
grep "Falha na verificação do nonce" /var/log/apache2/error.log | grep -o "IP: [0-9.]*" | sort | uniq -c | sort -nr
```

## 📋 Checklist de Logs a Verificar

### Backend Logs
- [ ] Timestamp das requisições
- [ ] IP do usuário
- [ ] User Agent
- [ ] Campos POST presentes
- [ ] Status da validação do nonce
- [ ] Status da validação reCAPTCHA
- [ ] Erros específicos

### Frontend Logs
- [ ] Scripts carregados
- [ ] Ordem de carregamento
- [ ] Erros de reCAPTCHA
- [ ] Estado do formulário
- [ ] Ações do usuário

### Logs de Sistema
- [ ] Erros 404 de scripts
- [ ] Timeouts de rede
- [ ] Problemas de cache
- [ ] Conflitos de plugins

## 🎯 Métricas de Sucesso

### Após Correção
- [ ] Redução significativa de erros "Erro de segurança"
- [ ] Aumento de submissões bem-sucedidas
- [ ] Melhoria na experiência do usuário
- [ ] Logs mais claros para debugging futuro

### Monitoramento Contínuo
- [ ] Taxa de erro < 5%
- [ ] Tempo médio de resposta < 3s
- [ ] Sucesso de reCAPTCHA > 95%
- [ ] Não há falsos positivos

---

**Status**: 📝 Template criado - Aguardando logs reais
**Próximo passo**: Implementar logs detalhados para coleta de dados</content>
<parameter name="filePath">/home/rafael/workspace/asaas-easy-subscription-plugin/docs/correcao-erro-seguranca/logs-producao.md