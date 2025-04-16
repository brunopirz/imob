# imob
real estate in php and mysql with integrated crm, webhook and external api requests

# Real Estate System

Complete system for property management with frontend for clients and administrative panel.

## Requirements

- PHP 8.0+
- MySQL 8.0+
- Apache/Nginx
- Composer (for dependencies)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/brunopirz/imob.git
cd imoveis



Instale as dependências:

bash
Copy
composer install
Configure o ambiente:

Copie .env.example para .env

Edite as configurações no arquivo .env

Importe os bancos de dados:

bash
Copy
mysql -u root -p balvedi_clientes < database/clientes.sql
mysql -u root -p balvedi_admin < database/admin.sql
Configure o virtual host do Apache para apontar para a pasta /public

Configure o cronjob para backups diários:

bash
Copy
0 2 * * * /usr/bin/php /caminho/para/o/projeto/scripts/backup.php
Configuração do .env
