<?php
namespace App\Services;

use Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Log;

class SettingsService {
    const STATUS_PASSWORD_NEED = 1;
    const STATUS_VERIFYED = 2;
    const STATUS_WRONG_CODE = 3;

    public function sendTelegramVerificationCode(string $phone): void
    {
        $MadelineProto = TelegramService::createMadelineProto($phone);
        Log::debug("Sending verification code to Telegram phone: $phone");
        $a = $MadelineProto->phoneLogin($phone);
        Log::debug("Verification code sent, response: " . json_encode($a));
    }

    public function verifyTelegramCode(int $code, string $phone): int {
        $MadelineProto = TelegramService::createMadelineProto($phone);
        Log::debug("Verifying Telegram code1: $code for phone: $phone");

        $authorization = $MadelineProto->completePhoneLogin($code);
        Log::debug("Authorization response1: " . json_encode($authorization));

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }

        $tgUser = Arr::get($authorization, 'user', []);

        $user = auth()->user()->load('account');

        Log::debug("User data loaded1: " . json_encode($user));

        $connectionData = [
            'phone' => $phone,
            'telegram_id' => Arr::get($tgUser, 'id'),
            'first_name' => Arr::get($tgUser, 'first_name'),
            'last_name' => Arr::get($tgUser, 'last_name'),
            'user_name' => Arr::get($tgUser, 'username', null),
        ];

        Log::debug("Connection data prepared1: " . json_encode($connectionData));

        $user->account->connections()->create($connectionData);
        Log::debug("Telegram user data sent1: " . json_encode($user->account->connections));

        Artisan::call("telegram-process", ["action" => 'start', "phone" => $phone]);
        return self::STATUS_VERIFYED;
    }

    public function verifyTelegramPassword(string $password, string $phone): int {
        $MadelineProto = TelegramService::createMadelineProto($phone);

        $authorization = $MadelineProto->complete2falogin($password);
        Log::debug("Verifying Telegram password: $password for phone: $phone, response: " . json_encode($authorization));

        if ($authorization['_'] === 'account.password') {
            return self::STATUS_PASSWORD_NEED;
        }
        $tgUser = Arr::get($authorization, 'user', []);
        $user = auth()->user()->load('account');
        Log::debug("User data loaded for password verification: " . json_encode($user));

        $connectionData = [
            'phone' => $phone,
            'telegram_id' => Arr::get($tgUser, 'id'),
            'first_name' => Arr::get($tgUser, 'first_name'),
            'last_name' => Arr::get($tgUser, 'last_name'),
            'user_name' => Arr::get($tgUser, 'username'),
        ];
        Log::debug("Connection data sent for password verification: " . json_encode($connectionData));

        $user->account->connections()->create($connectionData);
        Log::debug("Telegram user data sent for password verification: " . json_encode($user->account->connections));

        Artisan::call("telegram-process", ["action" => 'start', "phone" => $phone]);
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
