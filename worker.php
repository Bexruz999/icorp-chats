<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Listeners\TelegramIncomingMessage;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;


$app->make(Kernel::class)->bootstrap();

if ($argc < 2) {
    die("Ошибка: Укажите номер телефона как аргумент. Пример: php worker.php +998996042509\n");
}

$phone = $argv[1];


$storage = storage_path() . "/app/telegram/$phone.madeline";
TelegramIncomingMessage::startAndLoop($storage);
