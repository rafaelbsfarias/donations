# Documentação: includes/class-form-processor.php

## Visão Geral
Classe responsável por processar formulários de doação, sanitizando dados, interagindo com APIs e gerenciando fluxos de pagamento.

## Estrutura da Classe
- **Método Principal**: `process_form($form_data)` - orquestra todo o processamento.
- **Fluxo**:
  - Sanitização via `Asaas_Form_Sanitizer`.
  - Criação/verificação de cliente.
  - Tokenização de cartão (se aplicável).
  - Criação de pagamento ou assinatura.
  - Logs e retorno de resultado.

## Funcionalidades
- Suporte a doações únicas e recorrentes.
- Integração com cartões, PIX, boleto.
- Tratamento de erros e logs.

## Dependências
- `class-form-sanitizer.php`, `class-asaas-api.php`, `donation-log-functions.php`.

## Notas de Segurança
- Sanitização rigorosa de inputs.

## Considerações para Produção
- Método longo; pode ser dividido em métodos menores.