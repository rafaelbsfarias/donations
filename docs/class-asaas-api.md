# Documentação: includes/class-asaas-api.php

## Visão Geral
Classe facade que centraliza acesso às APIs do Asaas, instanciando clientes para diferentes operações (clientes, cartões, pagamentos, assinaturas).

## Estrutura da Classe
- **Propriedades Privadas**: Instâncias de clientes específicos.
- **Construtor**: Inicializa todos os clientes com base em configurações.
- **Métodos Públicos**: Getters para cada cliente (get_customers, get_credit_cards, etc.).

## Funcionalidades
- Abstrai a criação de instâncias de API.
- Usa configurações do admin (chave API, ambiente).

## Dependências
- Todas as classes da pasta `api/`.

## Notas de Segurança
- Verificação de classe existente para evitar conflitos.

## Considerações para Produção
- Singleton pattern pode ser considerado para evitar múltiplas instâncias.