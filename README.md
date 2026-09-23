<div align="center">
  <img src="https://github.com/user-attachments/assets/7899f31d-44a8-4101-b957-31ece24ecaf9" alt="Logo do Esquadrão da Alegria" width="200px">
</div>

# Projeto: Site Esquadrão da Alegria

## Visão Geral
O Esquadrão da Alegria é um grupo dedicado a trazer alegria e conforto aos pacientes hospitalizados, através de apresentações de palhaços. 

## Integrantes do Desenvolvimento
- **Bernardo Prates**: Desenvolvedor back-end
- **Jéssica Maria Silva**: Designer e desenvolvimento front-end
- **Rafael Reis**: Desenvolvedor
- **Silêncio Morais**: Designer

## Objetivos do Site
- Divulgar as atividades do Esquadrão da Alegria
- Compartilhar hospitais e parceiros do projeto
- Recrutar novos voluntários e apoiar doações

## Funcionalidades
- **Página Inicial**: Introdução ao Esquadrão da Alegria e formulário de contato
- **Conheça**: História do projeto, missão e valores
- **Hospitais**: Localização e divulgação dos hospitais parceiros 
- **Contato**: Formulário para novos voluntários e informações de doação

# Deploy Esquadrão da Alegria – VPS KingHost

## Setup inicial (foi feito em dev em 2025-11)

- Ubuntu 22.04 LTS
- PHP 8.4 (ppa:ondrej/php)
- Nginx
- MySQL 8
- Node.js 20

### Comandos principais

- composer install --no-dev --optimize-autoloader
- npm install && npm run build
- php artisan key:generate
- php artisan migrate --force

### Estrutura de diretórios

- /var/www/esquadraodaalegria/prod
- /var/www/esquadraodaalegria/dev

### Bancos

- esquadrao_alegria_dev (usuario_dev / senha)
- esquadrao_alegria_prod (usuario_prod / senha)

### Agendador (cron)

O Laravel Scheduler precisa rodar a cada minuto para disparar os comandos agendados em `routes/console.php` (finalização automática de eventos e processamento de lembretes de Web Push). Adicionar ao crontab do usuário da aplicação em cada ambiente (dev e prod):

```
* * * * * cd /var/www/esquadraodaalegria/prod && php artisan schedule:run >> /dev/null 2>&1
```

### Worker de fila (Supervisor)

As notificações de Web Push (`App\Jobs\Lembrete\Entrega\Job` e `App\Jobs\Lembrete\Processamento\Job`) são despachadas para a fila (`QUEUE_CONNECTION=database`) e exigem um worker persistente. Sem o worker ativo, os lembretes ficam enfileirados e não são entregues. Configurar um processo no Supervisor por ambiente:

```
[program:esquadrao-queue-worker-prod]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/esquadraodaalegria/prod/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/esquadraodaalegria/prod
autostart=true
autorestart=true
numprocs=1
user=www-data
stdout_logfile=/var/www/esquadraodaalegria/prod/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Após criar/alterar o arquivo `.conf` em `/etc/supervisor/conf.d/`, aplicar com:

```
supervisorctl reread
supervisorctl update
supervisorctl start esquadrao-queue-worker-prod:*
```

### Chaves VAPID (Web Push)

Gerar o par de chaves VAPID uma única vez por ambiente e configurar em `.env` (`VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`):

```
php artisan tinker --execute="print_r(Minishlink\WebPush\VAPID::createVapidKeys());"
```

### Limitações de Push por navegador/dispositivo

- **Desktop (Chrome, Edge, Firefox)**: suporte completo a Web Push, inclusive com o app fechado.
- **Android (Chrome)**: suporte completo, inclusive com o app fechado ou o PWA instalado.
- **iOS/iPadOS (Safari)**: Web Push só funciona se o usuário instalar o PWA na tela de início (`Adicionar à Tela de Início`); não há suporte a Push pelo Safari aberto em aba normal. A tela de preferências orienta essa instalação quando detecta o navegador sem suporte direto.
