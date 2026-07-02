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
