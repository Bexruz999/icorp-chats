<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TelegramProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram-process:start {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start telegram process loop to handle incoming messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');

        if(Storage::disk('local')->exists("telegram/$phone.madeline")) {
            $programName = "telegram_$phone";
            $command = "/usr/bin/php " . base_path() . "/worker.php $phone && chown -R www-data:www-data /var/www/storage/telegram/$phone.madeline";
            $config = "
            [program:$programName]
            command=$command
            autostart=true
            autorestart=true
            user=root
            stderr_logfile=/var/log/supervisor/$programName.err.log
            stdout_logfile=/var/log/supervisor/$programName.out.log
            ";

            file_put_contents("/etc/supervisor/conf.d/$programName.conf", $config);
            //file_put_contents("/etc/supervisord.d/$programName.conf", $config);
            exec("supervisorctl reread && supervisorctl update && supervisorctl start $programName");

        }  else {
            echo "Directory does not exists";
        }
    }
}
