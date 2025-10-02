# Documentação: includes/class-plugin-loader.php

## Visão Geral
Classe responsável por carregar e inicializar componentes do plugin de forma organizada.

## Estrutura da Classe
- **Método `init()`**: Carrega arquivos condicionalmente (admin vs frontend).

## Funcionalidades
- Carrega classes admin apenas no painel.
- Carrega scripts, shortcodes, API, AJAX, logs sempre.

## Dependências
- Todos os arquivos incluídos.

## Considerações
- Centraliza carregamento para evitar duplicatas.