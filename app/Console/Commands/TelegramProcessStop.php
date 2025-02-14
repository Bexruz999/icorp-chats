<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelegramProcessStop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram-process:stop {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');

        $programName = "telegram_$phone";
        exec("sudo supervisorctl stop $programName");
        //unlink("/etc/supervisord.d/$programName.conf");
        unlink("/etc/supervisor/conf.d/$programName.conf");
        exec("sudo supervisorctl reread && supervisorctl update");
    }
}
