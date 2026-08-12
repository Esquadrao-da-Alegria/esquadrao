# Ajustes de contabilização de visitas

## Objetivo

Permitir que um administrador corrija uma participação registrada incorretamente ou aceite, mediante justificativa, um relatório entregue fora do prazo. O fluxo atende solicitações recebidas fora do sistema e não substitui a inscrição ou o relatório regulares.

## Regras

- Somente administradores podem consultar e criar ajustes, inclusive administradores de suporte sem cidade base.
- Apenas visitas com status `realizada` podem ser ajustadas.
- O administrador não pode criar ajuste em benefício próprio.
- A correção de participação inclui ou confirma o voluntário e registra seu tipo como palhaço/artista ou paisana.
- O aceite exige um relatório existente, atrasado e de autor com participação confirmada. O campo `fora_do_prazo` permanece verdadeiro.
- Um relatório atrasado aceito de palhaço/artista valida os palhaços/artistas confirmados da visita. Para paisanas, valida somente seu autor.
- Cada registro guarda administrador, voluntário, visita, relatório quando aplicável, justificativa e estados anterior e posterior.
- O histórico é imutável nesta versão: não existem edição nem exclusão.
- Os dashboards identificam a contabilização decorrente de ajuste e deixam de tratar o relatório aceito como pendência de prazo.

## Evolução futura

Se o atendimento passar a ocorrer dentro do sistema, a solicitação do voluntário e sua aprovação devem ser vinculadas ao mesmo ajuste auditável, preservando os campos de autoria, justificativa e estados anterior/posterior.
