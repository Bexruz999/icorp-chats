<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
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
        return Inertia::render('Settings/Index');
    }

    public function createTelegramChat(): Response 
    {
        return Inertia::render('Settings/CreateTelegramChat', [
            "state" => self::STATE_SEND_CODE
        ]);
    }

    public function sendCode(Request $request)
    {
        // Validate phone number and password
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }
        $phone = $request->input("phone");
        $this->settingsService->sendTelegramVerificationCode($phone);
        
        return Inertia::render("Settings/CreateTelegramChat", [
            "state" => self::STATE_VERIFY_CODE,
            "phoneNumber" => $phone
        ]);
    }

    public function verifyCode (Request $request) {
        // Validate phone number and password
        $validator = Validator::make($request->all(), [
            'code' => 'required|integer',
            'phone' => 'required|string|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }
        $phone = $request->input("phone");
        $code = $request->input("code");

        $status = $this->settingsService->verifyTelegramCode($code, $phone);

        if($status == SettingsService::STATUS_PASSWORD_NEED) {
            return Inertia::render("Settings/CreateTelegramChat", [
                "state" => self::STATE_PASSWORD_VERIFY,
                "phoneNumber" => $phone
            ]);
        }

        return Inertia::render("Settings/Index");
    }

    public function verifyPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 401);
        }

        $phone = $request->input("phone");
        $password = $request->input("password");

        $this->settingsService->verifyTelegramPassword($password, $phone);

        return Redirect::route('settings')->with('success', 'Телеграм канал подключен');
    }

    public function deleteConnection(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15'
        ]);

        $this->settingsService->deleteConnection($request->input("phone"));
        return Redirect::route('settings')->with('success', 'Телеграм канал удален');
    }
}
