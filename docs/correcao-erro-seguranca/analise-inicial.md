# Correção: Erro de Segurança em Produção

## 📋 Problema Identificado

### Sintomas
- **Produção**: Erro "Erro de segurança. Recarregue a página e tente novamente." acompanhado de imagem de erro
- **Ambiente de Teste**: Funciona normalmente sem chaves reCAPTCHA
- **Produção**: Falha quando chaves reCAPTCHA estão configuradas

### Análise Técnica

#### Fluxo Atual Problemático
1. **Frontend** (`form-recaptcha.js`):
   - Intercepta submit do formulário
   - Tenta gerar token reCAPTCHA
   - **Se falhar**: Mostra alert, mas pode permitir continuação do fluxo

2. **Backend** (`ajax-handler.php`):
   - Verifica nonce primeiro (linha 71)
   - Se nonce falhar → retorna "Erro de segurança. Recarregue a página e tente novamente."
   - Só depois verifica reCAPTCHA

#### Causa Raiz
O problema **não é o nonce em si**, mas o **fluxo sendo interrompido pelo reCAPTCHA**:

- **Teste** (sem reCAPTCHA): Form enviado diretamente → Nonce válido → Sucesso
- **Produção** (com reCAPTCHA): reCAPTCHA intercepta → Pode falhar → Form enviado com estado inconsistente → Nonce falha → Erro

## 🎯 Hipóteses de Correção

### Hipótese 1: Ordem de Validação Invertida
**Problema**: Nonce é verificado antes do reCAPTCHA, mas reCAPTCHA pode invalidar o contexto.

**Solução**: Reordenar validações ou implementar fallback quando reCAPTCHA falha.

### Hipótese 2: Estado do Formulário Corrompido
**Problema**: Múltiplas interceptações do mesmo form causando estado inconsistente.

**Solução**: Unificar interceptação e melhorar controle de estado.

### Hipótese 3: Timing Issues
**Problema**: Scripts carregam fora de ordem ou reCAPTCHA não está pronto.

**Solução**: Melhorar dependências e adicionar verificações de prontidão.

## 📝 Plano de Correção

### Fase 1: Diagnóstico Detalhado
1. **Adicionar logs frontend** para rastrear fluxo completo
2. **Implementar debug mode** temporário em produção
3. **Criar script de teste** para reproduzir o erro
4. **Analisar logs** para confirmar causa raiz

### Fase 2: Correção do Fluxo
1. **Unificar interceptação** do formulário
2. **Implementar fallback** quando reCAPTCHA falha
3. **Melhorar tratamento de erros** no frontend
4. **Adicionar timeout** para reCAPTCHA

### Fase 3: Validações de Segurança
1. **Reordenar validações** no backend se necessário
2. **Implementar verificação condicional** do nonce
3. **Adicionar validação de estado** do formulário
4. **Melhorar mensagens de erro**

### Fase 4: Testes e Validação
1. **Testes em ambiente controlado**
2. **Validação em produção** com rollback plan
3. **Monitoramento pós-deploy**
4. **Documentação da correção**

## 🔧 Implementações Planejadas

### 1. Frontend: Unificar Interceptação
```javascript
// Unificar lógica de interceptação em um único ponto
// Implementar fallback quando reCAPTCHA falha
// Melhorar controle de estado do botão
```

### 2. Backend: Reordenar Validações
```php
// Possivelmente reordenar validações
// Implementar verificação condicional baseada no contexto
// Melhorar logs para diagnóstico
```

### 3. Debug Tools
```php
// Adicionar modo debug temporário
// Logs detalhados do fluxo
// Ferramentas de diagnóstico
```

## 📊 Critérios de Sucesso

### Funcional
- ✅ Formulário funciona em produção com reCAPTCHA
- ✅ Não há falsos positivos de erro de segurança
- ✅ Mensagens de erro são claras e úteis

### Segurança
- ✅ Nonce continua sendo validado corretamente
- ✅ reCAPTCHA continua protegendo contra bots
- ✅ Não há regressões de segurança

### Performance
- ✅ Não há impacto negativo na performance
- ✅ Scripts carregam na ordem correta
- ✅ Não há delays desnecessários

## 📅 Cronograma

### Semana 1: Diagnóstico
- [ ] Adicionar logs detalhados
- [ ] Implementar debug mode
- [ ] Analisar logs de produção
- [ ] Confirmar causa raiz

### Semana 2: Implementação
- [ ] Corrigir fluxo frontend
- [ ] Ajustar validações backend
- [ ] Implementar fallbacks
- [ ] Testes em desenvolvimento

### Semana 3: Testes e Deploy
- [ ] Testes em staging
- [ ] Deploy gradual
- [ ] Monitoramento
- [ ] Rollback se necessário

## 📋 Riscos e Mitigações

### Risco: Quebrar funcionalidade existente
**Mitigação**: Deploy gradual, testes extensivos, plano de rollback

### Risco: Impacto na segurança
**Mitigação**: Revisão de segurança, manter validações críticas

### Risco: Problemas de performance
**Mitigação**: Otimização de código, testes de carga

## 📚 Referências

- [Análise inicial do problema](docs/correcao-erro-seguranca/analise-inicial.md)
- [Logs de produção analisados](docs/correcao-erro-seguranca/logs-producao.md)
- [Testes realizados](docs/correcao-erro-seguranca/testes.md)

---

**Status**: 🔄 Em análise
**Prioridade**: 🔴 Crítica
**Responsável**: Rafael
**Data de início**: Outubro 2025</content>
<parameter name="filePath">/home/rafael/workspace/asaas-easy-subscription-plugin/docs/correcao-erro-seguranca/README.md