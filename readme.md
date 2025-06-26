# Ping CRM React

A demo application to illustrate how [Inertia.js](https://inertiajs.com/) works with [Laravel](https://laravel.com/) and [React](https://reactjs.org/).

> This is a port of the original [Ping CRM](https://github.com/inertiajs/pingcrm) written in Laravel and Vue.

![](https://raw.githubusercontent.com/liorocks/pingcrm-react/master/screenshot.png)

## Installation

Clone the repo locally:

```sh
git clone https://github.com/liorocks/pingcrm-react.git
cd pingcrm-react
```

Install PHP dependencies:

```sh
composer install
```

Install NPM dependencies:

```sh
npm install
```

Build assets:

```sh
npm run dev
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Run artisan server:

```sh
php artisan serve
```

You're ready to go! [Visit Ping CRM](http://127.0.0.1:8000/) in your browser, and login with:

- **Username:** johndoe@example.com
- **Password:** secret

## Running tests

To run the Ping CRM tests, run:

```
php artisan test
```

## Credits

- Original work by Jonathan Reinink (@reinink) and contributors
- Port to Ruby on Rails by Georg Ledermann (@ledermann)
- Port to React by Lio (@liorocks)

Deployment Guide
Requirements
PHP 8.1+
Composer
Node.js & npm
Redis server
MySQL or SQLite
Supervisor
Nginx or Apache
Reverb (for broadcasting)
SMTP server (for registration and email notifications)
Permissions
Make sure the following directories are writable by the web server user (www-data):
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
Supervisor Setup
Copy the provided supervisor_config_watcher.service and supervisor_config_watcher.sh to your server.
Edit /etc/sudoers and add:
www-data ALL=(root) NOPASSWD: /usr/bin/supervisorctl, /bin/unlink
Register the watcher service:
sudo cp supervisor_config_watcher.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable supervisor_config_watcher
sudo systemctl start supervisor_config_watcher
Worker Setup
Configure Supervisor to run Laravel queue and websocket workers. Example /etc/supervisor/conf.d/laravel-worker.conf:
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-worker.log

[program:reverb]
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/reverb.log
Reload Supervisor:
sudo supervisorctl reread
sudo supervisorctl update
Redis
Install and start Redis:
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
Reverb
Install Reverb as per Laravel docs and configure .env:
BROADCAST_DRIVER=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
SMTP
Set up your SMTP credentials in .env for registration and notifications:
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="Your App"
Build & Run
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan serve
Visit the App
Open http://127.0.0.1:8000/ in your browser.
<hr></hr>
Let me know if you want this written directly to your readme.md file.
