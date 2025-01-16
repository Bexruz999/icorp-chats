<?php
namespace App\Services;

use App\Models\Connection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SettingsService {
    const STATUS_PASSWORD_NEED = 1;
    const STATUS_VERIFYED = 2;
    const STATUS_WRONG_CODE = 3;

    public function sendTelegramVerificationCode(string $phone): void
    {
        $MadelineProto = $this->createMadelineProto($phone);
        $user = auth()->user()->load('account');

        $user->account->connections()->create([
            'phone' => $phone
        ]);

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

    public function deleteConnection(string $phone) {
        $storagePath = $this->getStoragePath($phone);
        File::deleteDirectory($storagePath);
        DB::table('connections')->where(['phone' => $phone, 'account_id' => auth()->user()->account->id])->delete();
    }

    private function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = new \danog\MadelineProto\Settings;
        // $settings->setDb(
        //     (new \danog\MadelineProto\Settings\Database\Postgres)
        //     ->setDatabase(env("DB_DATABASE"))
        //     ->setUsername(env("DB_USERNAME"))
        //     ->setPassword(env("DB_PASSWORD"))
        // );

        $settings->setAppInfo(
            (new \danog\MadelineProto\Settings\AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'))
        );

        $storagePath = $this->getStoragePath($phone);

        return new \danog\MadelineProto\API($storagePath, $settings);
    }

    private function getStoragePath(string $phone): string {
        return storage_path("app/telegram/{$phone}.madeline");
    }
}
