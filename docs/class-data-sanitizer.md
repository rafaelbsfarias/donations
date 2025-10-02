# Documentação: includes/sanitization/class-data-sanitizer.php

## Visão Geral
Classe com métodos estáticos para sanitização de dados específicos (text, email, números, CPF/CNPJ, etc.).

## Métodos
- `sanitize_text`, `sanitize_email`, `sanitize_numbers_only`, etc.
- Métodos específicos para cartão, telefone, CEP.

## Funcionalidades
- Usa funções WordPress e regex para limpeza.

## Dependências
- Funções WordPress.

## Considerações
- Métodos estáticos para reutilização.