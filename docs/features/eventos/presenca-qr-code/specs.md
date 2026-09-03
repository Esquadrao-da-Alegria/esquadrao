# Confirmação de presença em eventos por QR Code

## Objetivo

Permitir que participantes presentes em reuniões e oficinas confirmem a própria presença de forma simples, autenticada e auditável.

## Permissões e janela de abertura

- Administradores, o responsável e o criador do evento podem abrir, visualizar e encerrar a confirmação.
- A confirmação por QR Code é exclusiva para reuniões e oficinas; ações especiais mantêm o fluxo de presença existente.
- O evento precisa estar `agendado` e ocorrer na data atual.
- A sessão pode ser aberta a partir de uma hora antes do início e permanece disponível até ser encerrada ou o evento ser finalizado ou cancelado.
- Apenas uma sessão pode permanecer ativa por evento. Uma reabertura cria uma nova sessão e não recupera os códigos anteriores.

## QR Code e autenticação

- O QR identifica somente o evento e sua sessão de presença; ele nunca contém o usuário participante.
- O endereço é assinado pelo backend, expira em dois minutos e é renovado periodicamente na tela do responsável.
- O encerramento da sessão invalida imediatamente todos os códigos emitidos, mesmo que a assinatura ainda não tenha expirado.
- A entrada assinada é validada antes do login e o contexto fica restrito à sessão do navegador. Depois da autenticação, o usuário retorna à confirmação sem depender do prazo restante do QR original.
- A presença somente é registrada depois que o participante seleciona **Confirmar minha presença**.

## Registro da presença

- Uma participação ativa é localizada e marcada como `presente` sem alterar a data original da inscrição.
- Uma inscrição cancelada é reativada e marcada como `presente`.
- Um usuário ainda não inscrito é incluído e marcado como `presente`.
- A confirmação presencial supera o prazo de inscrição e o limite configurado de participantes.
- A restrição única por evento e usuário impede múltiplas confirmações, inclusive em requisições concorrentes.
- A presença utiliza os mesmos campos já considerados nos dashboards e pode ser corrigida posteriormente pelos fluxos administrativos existentes.

## Auditoria

Cada confirmação registra o evento, participante, sessão utilizada, responsável pela abertura, data e hora e o método `QR_CODE`. O histórico permanece separado da participação para preservar a evidência mesmo se houver uma correção manual posterior.

IP e identificação do dispositivo não são coletados nesta versão. Esses dados podem ser adicionados à confirmação no futuro caso exista necessidade operacional e definição adequada de privacidade.

## Interface

- A tela do responsável apresenta o QR, a quantidade confirmada, nomes e horários, com atualização periódica sem WebSocket.
- A tela do participante apresenta os dados essenciais do evento e uma ação explícita de confirmação.
- Códigos inválidos ou alterados recebem bloqueio, códigos de sessões encerradas recebem indicação de indisponibilidade e usuários não autenticados são conduzidos ao login.
