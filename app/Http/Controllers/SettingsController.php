<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\AmoConnection;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    protected SettingsService $settingsService;

    const STATE_SEND_CODE = 1;
    const STATE_VERIFY_CODE = 2;
    const STATE_PASSWORD_VERIFY = 3;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index(): Response
    {
        $accountId = auth()->user()->account->id;

        return Inertia::render('Settings/Index', [
            'connections' => Connection::where('account_id', $accountId)->get(),
            'amo_connections' => AmoConnection::query()->where('account_id', $accountId)->get(),
        ]);
    }

    public function createTelegramChat(): Response
    {
        return Inertia::render('Settings/CreateTelegramChat', [
            'state' => self::STATE_SEND_CODE,
        ]);
    }

    public function sendCode(Request $request): Response|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        $phone = $request->input('phone');
        $this->settingsService->sendTelegramVerificationCode($phone);

        return Inertia::render('Settings/CreateTelegramChat', [
            'state' => self::STATE_VERIFY_CODE,
            'phoneNumber' => $phone,
        ]);
    }

    public function verifyCode(Request $request): Response|RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|integer',
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        $phone = $request->input('phone');
        $code = $request->input('code');

        $status = $this->settingsService->verifyTelegramCode($code, $phone);


        if ($status === SettingsService::STATUS_PASSWORD_NEED) {
            return Inertia::render('Settings/CreateTelegramChat', [
                'state' => self::STATE_PASSWORD_VERIFY,
                'phoneNumber' => $phone,
            ]);
        }

        return redirect()->route('settings')->with('success', 'Телеграм канал подключен');
    }

    public function verifyPassword(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:4',
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        $phone = $request->input('phone');
        $password = $request->input('password');

        $this->settingsService->verifyTelegramPassword($password, $phone);

        return redirect()->route('settings')->with('success', 'Телеграм канал подключен');
    }

    // Removed unused $request parameter
    public function deleteConnection(int $id): RedirectResponse
    {
        $connection = Connection::findOrFail($id);
        $this->settingsService->deleteConnection($connection->phone);

        return redirect()->back()->with('success', 'Телеграм канал удален');
    }

    public function createAmoConnection(): Response
    {
        return Inertia::render('Settings/AmoConnectionForm', [
            'amo_connection' => null,
        ]);
    }

    public function storeAmoConnection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'uid' => 'required|string',
            'amojo_id' => 'required|string',
            'secret_key' => 'required|string',
            'amo_account_id' => 'required|string',
            'base_domain' => 'required|string', // domain -> base_domain
        ]);

        $data['account_id'] = auth()->user()->account->id;

        AmoConnection::query()->create($data);

        return redirect()->route('settings')->with('success', 'AmoCRM connection added');
    }

    public function editAmoConnection(AmoConnection $amo_connection): Response
    {
        return Inertia::render('Settings/AmoConnectionForm', [
            'amo_connection' => $amo_connection,
        ]);
    }

    public function updateAmoConnection(Request $request, AmoConnection $amo_connection): RedirectResponse
    {
        $data = $request->validate([
            'uid' => 'required|string',
            'amojo_id' => 'required|string',
            'secret_key' => 'required|string',
            'amo_account_id' => 'required|string',
            'base_domain' => 'required|string', // domain -> base_domain
        ]);

        $amo_connection->update($data);

        return redirect()->route('settings')->with('success', 'AmoCRM connection updated');
    }

    public function deleteAmoConnection($id): RedirectResponse
    {
        $amo = AmoConnection::findOrFail($id);
        $amo->delete();
        return redirect()->route('settings')->with('success', 'AmoCRM connection deleted');
    }
}
