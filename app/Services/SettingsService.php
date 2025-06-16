<?php
namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SettingsService {
    const STATUS_PASSWORD_NEED = 1;
    const STATUS_VERIFYED = 2;
    const STATUS_WRONG_CODE = 3;

    public function sendTelegramVerificationCode(string $phone): void
    {
        $MadelineProto = TelegramService::createMadelineProto($phone);

        $MadelineProto->phoneLogin($phone);
    }

    public function verifyTelegramCode(int $code, string $phone): int {
        $MadelineProto = TelegramService::createMadelineProto($phone);

        $authorization = $MadelineProto->completePhoneLogin($code);

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }

        $user = auth()->user()->load('account');

        // Obtaining user data
        $me = $MadelineProto->getSelf();
        $connectionData = [
            'phone' => $phone,
            'telegram_id' => $me['id'] ?? null,
            'first_name' => $me['first_name'] ?? null,
            'last_name' => $me['last_name'] ?? null,
        ];
        if (!empty($me['username'])) {
            $connectionData['user_name'] = $me['username'];
        }

        $user->account->connections()->create($connectionData);

        Artisan::call("telegram-process", ["action" => 'start', "phone" => $phone]);
        return self::STATUS_VERIFYED;
    }

    public function verifyTelegramPassword(string $password, string $phone): int {
        $MadelineProto = TelegramService::createMadelineProto($phone);

        $authorization = $MadelineProto->complete2falogin($password);

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }

        $user = auth()->user()->load('account');

        $user->account->connections()->create([
            'phone' => $phone
        ]);

        Artisan::call("telegram-process:start", ["phone" => $phone]);
        return self::STATUS_VERIFYED;
    }

    public function deleteConnection(string $phone) {
        // Logout from Telegram
        try {
            $MadelineProto = TelegramService::createMadelineProto($phone);
            $MadelineProto->logOut();
        } catch (\Throwable $e) {
            // If there is an error in the Logout, the silent is transferred
        }

        $storagePath = TelegramService::getStoragePath($phone);
        File::deleteDirectory($storagePath);
        Artisan::call("telegram-process", ["action" => 'stop', "phone" => $phone]);
        DB::table('connections')->where(['phone' => $phone, 'account_id' => auth()->user()->account->id])->delete();
    }
}
