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
    protected $signature = 'telegram-process {action} {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start or stop telegram process loop to handle incoming messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $phone = $this->argument('phone');
        $programName = "telegram_$phone";
        $localConfigPath = storage_path("app/supervisor_$programName.conf");

        if ($action === 'start') {
            if (Storage::disk('local')->exists("telegram/$phone.madeline")) {
                $command = "/usr/bin/php " . base_path() . "/worker.php $phone";
                $config = "
[program:$programName]
command=$command
autostart=true
autorestart=true
user=www-data
stderr_logfile=/var/log/supervisor/$programName.err.log
stdout_logfile=/var/log/supervisor/$programName.out.log
";
                file_put_contents($localConfigPath, $config);
                $this->info("Created Supervisor config file: $localConfigPath");
                $this->info("Root has the right to serve this file automatically /etc/supervisor/conf.d/ ga ko'chiradi va supervisorctl reload qiladi.");
            } else {
                $this->error("Madeline file not available: telegram/$phone.madeline");
            }
        } elseif ($action === 'stop') {
            // Delete config file in local and stop process
            if (file_exists($localConfigPath)) {
                unlink($localConfigPath);
                $this->info("Local config file deleted: $localConfigPath");
            } else {
                $this->info("Local config file not found: $localConfigPath");
            }
            // Stop the process if the supervisor is installed in the local
            if (app()->environment('local')) {
                $stopCmd = "supervisorctl stop $programName";
                exec($stopCmd, $output, $code);
                if ($code === 0) {
                    $this->info("Supervisor process discontinued: $programName");
                } else {
                    $this->info("Supervisor process not found or stopped: $programName");
                }
                // Trying to delete the Config file too
                $etcConfigPath = "/etc/supervisor/conf.d/$programName.conf";
                if (file_exists($etcConfigPath)) {
                    @unlink($etcConfigPath);
                    exec("supervisorctl reread && supervisorctl update");
                    $this->info("Supervisor config file deleted and reloaded: $etcConfigPath");
                }
            }
        } else {
            $this->error("Incorrect action. Can only be 'start' or 'stop'.");
        }
    }
}
