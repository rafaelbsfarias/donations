# Changelog - Asaas Easy Subscription Plugin

Todas as mudanças significativas neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-10-16

### ✅ Corrigido
- **Erro crítico de segurança**: Resolvido o erro "Erro de segurança. Recarregue a página e tente novamente" que ocorria quando reCAPTCHA estava configurado
- **Conflito de interceptação**: Unificado o controle de submissão de formulários entre `form-recaptcha.js` e `form-ajax.js`
- **Fallback reCAPTCHA**: Implementado fallback automático que permite processamento mesmo quando reCAPTCHA falha
- **Sistema de logs**: Adicionado logging estruturado para falhas reCAPTCHA em arquivo dedicado
- **Dependências de scripts**: Corrigida ordem de carregamento dos scripts JavaScript

### 🔧 Alterado
- **Backend (ajax-handler.php)**: Refatorada verificação reCAPTCHA com função helper `verify_recaptcha_token()`
- **Frontend (form-recaptcha.js)**: Reescrito para controle unificado de estado do formulário
- **Scripts (enqueue-scripts.php)**: Melhorada gestão de dependências e carregamento condicional

### 📊 Adicionado
- **Monitoramento avançado**: Sistema completo de métricas e alertas para produção
- **Logs estruturados**: Arquivo dedicado `/wp-content/asaas-recaptcha-failures.log` para análise
- **Queries de monitoramento**: SQL queries para acompanhar KPIs em tempo real
- **Plano de rollback**: Script automatizado para reversão em caso de problemas

### 🛡️ Segurança
- **Fail-safe design**: Sistema continua funcionando mesmo com falhas parciais
- **Logging de segurança**: Rastreamento completo de tentativas suspeitas
- **Validação robusta**: Verificações múltiplas de integridade

## [1.0.0] - 2025-10-XX

### ✅ Adicionado
- **Funcionalidade inicial**: Integração completa com Asaas API
- **Pagamentos únicos e recorrentes**: Suporte a PIX, Boleto e Cartão
- **Interface administrativa**: Configurações via WordPress Settings API
- **Shortcodes**: Templates para formulários de doação
- **Segurança**: Nonces, sanitização e validações
- **reCAPTCHA v3**: Proteção contra bots
- **Logging**: Sistema de logs para depuração

### 🏗️ Infraestrutura
- **Arquitetura modular**: Classes organizadas por responsabilidade
- **WordPress standards**: Compatível com melhores práticas
- **Documentação**: Arquivos markdown detalhados
- **Testes**: Estrutura preparada para testes automatizados

---

## Guia de Versionamento

Este projeto segue [Semantic Versioning](https://semver.org/):

- **MAJOR**: Mudanças incompatíveis (breaking changes)
- **MINOR**: Novas funcionalidades compatíveis
- **PATCH**: Correções de bugs compatíveis

### Tipos de Mudança
- `✅ Corrigido`: Bugs corrigidos
- `🔧 Alterado`: Mudanças em funcionalidade existente
- `📊 Adicionado`: Novas funcionalidades
- `🗑️ Removido`: Funcionalidades removidas
- `🚨 Segurança`: Correções de segurança
- `🏗️ Infraestrutura`: Mudanças na estrutura/código

### Status da Versão
- 🟢 **Estável**: Testado em produção
- 🟡 **Beta**: Funcional mas precisa de testes
- 🔴 **Alpha**: Em desenvolvimento