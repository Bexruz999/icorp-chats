<?php
namespace App\Services;
class TelegramService {

    public static function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = (new \danog\MadelineProto\Settings\AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = storage_path("app/telegram/{$phone}.madeline");

        return new \danog\MadelineProto\API($storagePath, $settings);
    }

}



