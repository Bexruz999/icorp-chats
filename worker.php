<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Listeners\TelegramIncomingMessage;
use Illuminate\Contracts\Console\Kernel;

$app->make(Kernel::class)->bootstrap();

$storage = storage_path() . '/app/telegram/+998996042509.madeline';
TelegramIncomingMessage::startAndLoop($storage);