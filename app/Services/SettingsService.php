<?php
namespace App\Services;

class SettingsService {
    const STATUS_PASSWORD_NEED = 1;
    const STATUS_VERIFYED = 2;
    const STATUS_WRONG_CODE = 3;

    public function sendTelegramVerificationCode(string $phone): void 
    {
        $MadelineProto = $this->createMadelineProto($phone);
        $MadelineProto->phoneLogin($phone);
    }

    public function verifyTelegramCode(int $code, string $phone): int {
        $MadelineProto = $this->createMadelineProto($phone);

        $authorization = $MadelineProto->completePhoneLogin($code);

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }

        return self::STATUS_VERIFYED;
    }

    public function verifyTelegramPassword(string $password, string $phone): int {
        $MadelineProto = $this->createMadelineProto($phone);

        $authorization = $MadelineProto->complete2falogin($password);

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }

        return self::STATUS_VERIFYED;
    }

    private function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = (new \danog\MadelineProto\Settings\AppInfo)
        ->setApiId(intval(env("TELEGRAM_API_ID")))
        ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = storage_path("app/telegram/{$phone}.madeline");

        return new \danog\MadelineProto\API($storagePath, $settings);
    }
}