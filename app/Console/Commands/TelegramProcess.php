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

        if(Storage::exists("telegram/$phone.madeline")) {
            $programName = "telegram_$phone";
            $command = "/usr/bin/php " . base_path() . "/worker.php $phone";
            $config = "
            [program:$programName]
            command=$command
            autostart=true
            autorestart=true
            stderr_logfile=/var/log/supervisor/$programName.err.log
            stdout_logfile=/var/log/supervisor/$programName.out.log
            ";
            
            // file_put_contents("/etc/supervisor/conf.d/$programName.conf", $config);
            file_put_contents("/etc/supervisord.d/$programName.conf", $config);
            exec("sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start $programName");
            
        }  else {
            echo "Directory does not exists";
        }
    }
}
