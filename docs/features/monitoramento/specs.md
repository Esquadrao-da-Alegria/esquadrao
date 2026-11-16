# Monitoramento básico

## Indicadores e acesso

| Pergunta | Onde consultar |
|---|---|
| O sistema está online? | Monitor externo do UptimeRobot apontando para `/up`. |
| Há requisições lentas? | `/pulse`, card **Slow Requests**: rotas que levaram pelo menos 2 segundos, quantidade e maior duração no período. |
| Há erros? | `/pulse`, card **Exceptions**, e logs do Laravel para detalhes e respostas HTTP 5xx. |

O Pulse exige login e cargo `administrador`, inclusive no ambiente local. O painel usa os componentes nativos do pacote em Blade/Livewire; as telas do sistema continuam em React.

## Health check

`GET /up` é público, não exige sessão e verifica a inicialização do Laravel e uma consulta `SELECT 1` no banco. Retorna 200 quando saudável ou 500 quando a verificação falha. Manter `APP_DEBUG=false` em produção.

Essa verificação não testa todas as funcionalidades nem o navegador. Por padrão do Laravel, `/up` continua acessível durante o modo de manutenção.

## Ativação em produção

O Pulse grava no banco existente, sem Redis ou processos adicionais. O deploy já instala as dependências e executa as migrations, incluindo as três tabelas `pulse_*`.

Após publicar o código, conferir estas variáveis no `.env` da VPS:

```dotenv
APP_DEBUG=false
PULSE_ENABLED=true
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=error
```

Então atualizar o cache:

```sh
php artisan config:cache
```

Abrir `/up` e depois `/pulse` com um administrador. O painel pode começar vazio: só aparecem requisições lentas e exceções ocorridas após a ativação. Os dados do Pulse têm retenção padrão de 7 dias, com limpeza automática durante a coleta. Para interromper a coleta, definir `PULSE_ENABLED=false` e atualizar o cache.

## UptimeRobot e alerta de queda

O monitor e os destinatários são configurados na conta da equipe, fora do repositório. Este código não cria a conta nem ativa alertas automaticamente.

1. Na conta do [UptimeRobot](https://uptimerobot.com/), criar um monitor **HTTP(s)** chamado `Esquadrão da Alegria — Produção`.
2. Usar a URL HTTPS de produção terminando em `/up`, sem login ou redirecionamento.
3. Selecionar o intervalo gratuito de **5 minutos**.
4. Confirmar o e-mail de alertas da equipe e vinculá-lo ao monitor para notificações de queda e recuperação.
5. Conferir que o monitor mostra **Up** e usar o teste de notificação do serviço para validar o recebimento do e-mail.
6. Em homologação, validar uma falha e a recuperação com um monitor separado, sem derrubar a produção.

Consultar disponibilidade, incidentes e tempo de resposta das sondagens no painel do UptimeRobot. O intervalo de 5 minutos pode deixar passar quedas breves e não garante alerta imediato. O tempo de `/up` não representa o tempo das demais rotas; para elas, consultar o Pulse.

Referência: [configuração de um monitor](https://help.uptimerobot.com/en/articles/11358364-how-to-create-your-first-monitor-on-uptimerobot-quick-setup-guide).

## Investigação de problemas

1. Anotar o horário e a ação relatada pelo usuário. Comparar os horários considerando o fuso exibido em cada ferramenta.
2. Consultar os incidentes no UptimeRobot. Se `/up` estiver indisponível, verificar Nginx, PHP-FPM, MySQL e espaço em disco na VPS.
3. Se o sistema estiver online, acessar `/pulse` e selecionar o período do problema. Em **Slow Requests**, identificar a rota e a maior duração; em **Exceptions**, identificar a classe, o arquivo e a frequência do erro.
4. Consultar `storage/logs/laravel-AAAA-MM-DD.log` no servidor. Procurar por `Resposta HTTP 5xx` e pelas exceções próximas do horário relatado. O registro de 5xx contém somente status, método e modelo da rota, sem valores dos parâmetros, query string ou corpo da requisição.
5. Para erros gerados antes do Laravel, como 502/504 do proxy, consultar os logs de acesso e erro do Nginx nos caminhos configurados na VPS.

Exemplo de consulta no diretório da aplicação:

```sh
tail -n 200 storage/logs/laravel-$(date +%F).log
rg 'Resposta HTTP 5xx' storage/logs/laravel-*.log
```

Se `rg` não estiver instalado na VPS, usar `grep` na segunda consulta. Os arquivos diários são mantidos por 14 dias e removidos durante novas gravações. Ambientes que ainda usam `LOG_STACK=single` continuam gravando em `storage/logs/laravel.log` até atualizar a variável.

O card de exceções não é um contador de respostas 5xx: uma resposta 500 devolvida sem exceção aparece nos logs, e uma exceção reportada pode ocorrer sem resposta 500. Esta versão não cria gráficos próprios de 5xx nem mede o desempenho do React no navegador.
