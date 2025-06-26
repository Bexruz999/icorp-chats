Deployment Guide

Requirements
------------
- PHP 8.1+
- Composer
- Node.js & npm
- Redis server
- MySQL or SQLite
- Supervisor
- Nginx or Apache
- Laravel Reverb (for broadcasting)
- SMTP server (for registration and notifications)

1. Clone the repository

   git clone https://github.com/liorocks/pingcrm-react.git
   cd pingcrm-react

2. Install dependencies

   composer install --optimize-autoloader --no-dev
   npm install

3. Build assets

   npm run build

4. Configure environment

   cp .env.example .env
   php artisan key:generate

   Edit .env and set:
  - APP_ENV=production
  - APP_URL=https://your-domain.com
  - Database credentials
  - SMTP credentials:
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.example.com
    MAIL_PORT=587
    MAIL_USERNAME=your@email.com
    MAIL_PASSWORD=yourpassword
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=your@email.com
    MAIL_FROM_NAME="Your App"
  - Redis config:
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
  - Reverb config:
    BROADCAST_DRIVER=reverb
    REVERB_HOST=127.0.0.1
    REVERB_PORT=8080

5. Set permissions

   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache

6. Database setup

   php artisan migrate --force
   php artisan db:seed --force

7. Configure Supervisor for workers

   Create /etc/supervisor/conf.d/laravel-worker.conf:

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
   sudo supervisorctl start laravel-worker:*
   sudo supervisorctl start reverb

8. Supervisor config watcher (optional)

   Copy supervisor_config_watcher.service and supervisor_config_watcher.sh to your server.

   Register the watcher service:

   sudo cp supervisor_config_watcher.service /etc/systemd/system/
   sudo systemctl daemon-reload
   sudo systemctl enable supervisor_config_watcher
   sudo systemctl start supervisor_config_watcher

9. Sudoers for www-data

   Edit with sudo visudo and add:

   www-data ALL=(root) NOPASSWD: /usr/bin/supervisorctl, /bin/unlink

10. Redis

    sudo apt install redis-server
    sudo systemctl enable redis-server
    sudo systemctl start redis-server

11. Web server

    Configure Nginx or Apache to serve the public directory.

    Example Nginx config:

    server {
    server_name your-domain.com;
    root /var/www/pingcrm-react/public;

        index index.php index.html;
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }

        location ~ /\.ht {
            deny all;
        }
    }

    Reload Nginx:

    sudo systemctl reload nginx

12. Run Laravel caches

    php artisan config:cache
    php artisan route:cache

13. Visit the App

    Open http://your-domain.com in your browser.
