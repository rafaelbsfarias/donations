# Fase 2: Refatoração de Código

Foco em melhorar a estrutura do código sem alterar funcionalidades, facilitando manutenção futura.

## 1. Dividir Métodos Longos
- **Problema**: `process_form` em `class-form-processor.php` é muito longo.
- **Solução**: Extrair em métodos menores (e.g., `process_customer`, `process_payment`).
- **Impacto**: Código mais legível.
- **Risco**: Baixo (refatoração interna).
- **Teste**: Fluxos de doação intactos.

## 2. Centralizar Sanitização
- **Problema**: Sanitização espalhada.
- **Solução**: Melhorar `class-data-sanitizer.php` e usar consistentemente.
- **Impacto**: Padronização.
- **Risco**: Baixo.
- **Teste**: Validar sanitização em todos campos.

## 3. Abstrair Dependências JS
- **Problema**: Verificações repetidas de dependências em JS.
- **Solução**: Melhorar objeto `deps` em `form-ajax.js`.
- **Impacto**: Menos duplicação.
- **Risco**: Baixo.
- **Teste**: Funcionalidades JS intactas.

## 4. Melhorar Estrutura de Templates
- **Problema**: Campos de cartão duplicados.
- **Solução**: Template parcial para campos de cartão.
- **Impacto**: Reutilização.
- **Risco**: Baixo.
- **Teste**: Formulários renderizam corretamente.

## Cronograma
- Semana 1-2: Refatorar process_form e sanitização.
- Semana 3-4: Melhorar JS e templates.

## Critérios de Conclusão
- Cobertura de testes unitários >50%.
- Código mais modular.