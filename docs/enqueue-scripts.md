# Documentação: includes/enqueue-scripts.php

## Visão Geral
Função que registra e enfileira estilos e scripts do plugin no frontend.

## Funcionalidades
- Registra CSS e JS com dependências corretas.
- Enfileira scripts em ordem (utils, masks, ui, ajax, script).
- Localiza script com `ajax_object` para AJAX.
- Logs de debug se WP_DEBUG ativo.

## Dependências
- jQuery.

## Considerações
- Caminhos relativos ao plugin.