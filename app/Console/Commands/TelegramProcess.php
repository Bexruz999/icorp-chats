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
                $this->info("Supervisor config fayli yaratildi: $localConfigPath");
                $this->info("Root huquqiga ega servis bu faylni avtomatik /etc/supervisor/conf.d/ ga ko'chiradi va supervisorctl reload qiladi.");
            } else {
                $this->error("Madeline fayli mavjud emas: telegram/$phone.madeline");
            }
        } elseif ($action === 'stop') {
            // Localda config faylini o'chirish va processni to'xtatish
            if (file_exists($localConfigPath)) {
                unlink($localConfigPath);
                $this->info("Local config fayli o'chirildi: $localConfigPath");
            } else {
                $this->info("Local config fayli topilmadi: $localConfigPath");
            }
            // Localda supervisor o'rnatilgan bo'lsa, processni to'xtatish
            if (app()->environment('local')) {
                $stopCmd = "supervisorctl stop $programName";
                exec($stopCmd, $output, $code);
                if ($code === 0) {
                    $this->info("Supervisor process to'xtatildi: $programName");
                } else {
                    $this->info("Supervisor process topilmadi yoki to'xtatilmadi: $programName");
                }
                // Config faylini ham o'chirishga harakat qilish
                $etcConfigPath = "/etc/supervisor/conf.d/$programName.conf";
                if (file_exists($etcConfigPath)) {
                    @unlink($etcConfigPath);
                    exec("supervisorctl reread && supervisorctl update");
                    $this->info("Supervisor config fayli o'chirildi va reload qilindi: $etcConfigPath");
                }
            }
        } else {
            $this->error("Noto'g'ri action. Faqat 'start' yoki 'stop' bo'lishi mumkin.");
        }
    }
}
