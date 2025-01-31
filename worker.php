<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Listeners\TelegramIncomingMessage;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;


$app->make(Kernel::class)->bootstrap();


$storage = storage_path() . '/app/telegram/+6282211915445.madeline';
TelegramIncomingMessage::startAndLoop($storage);


// Получаем все номера телефонов из БД
//$phones = DB::table('connections')->pluck('phone')->toArray();

if (empty($phones)) {
    die("Нет активных номеров для запуска Telegram worker.\n");
}

foreach ($phones as $phone) {
    $storage = storage_path() . "/app/telegram/{$phone}.madeline";
    echo "Запускаем обработчик для номера: {$phone}\n";

    TelegramIncomingMessage::startAndLoop($storage);
}

