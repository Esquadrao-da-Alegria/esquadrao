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
