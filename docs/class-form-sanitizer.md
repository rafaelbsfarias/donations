# Documentação: includes/sanitization/class-form-sanitizer.php

## Visão Geral
Classe para sanitização e validação completa de dados de formulários de doação.

## Método Principal
- `sanitize_form($form_data)`: Processa cada campo, sanitiza e valida.

## Campos Validados
- Nome, email, CPF/CNPJ, valor, telefone, CEP, etc.
- Campos específicos por método de pagamento.

## Funcionalidades
- Retorna array com dados sanitizados e erros.
- Validações de tamanho, formato, obrigatoriedade.

## Dependências
- `class-data-sanitizer.php`.

## Considerações
- Método extenso; pode ser dividido.