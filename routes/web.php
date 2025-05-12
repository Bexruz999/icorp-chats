<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\OrganizationsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\AdminValid;
use App\Http\Middleware\SetSpatieTeamContext;
use danog\MadelineProto\API;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MessengerController;
use Inertia\Inertia;
use Ufee\Amo\Base\Storage\Oauth\FileStorage;
use Ufee\Amo\Oauthapi;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// LOGIN AND REGISTER

Route::get('/register', [RegisterController::class, 'index'])
    ->name('register')
    ->middleware('guest');

Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store')
    ->middleware('guest');

Route::get('login', [LoginController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [LoginController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [LoginController::class, 'destroy'])
    ->name('logout');

// AUTHENTICATED ROUTES

Route::middleware(['auth', SetSpatieTeamContext::class])->group(function () {
    // Dashboard

    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Users

    Route::get('users', [UsersController::class, 'index'])
        ->name('users');

    Route::get('users/create', [UsersController::class, 'create'])
        ->name('users.create');

    Route::post('users', [UsersController::class, 'store'])
        ->name('users.store');

    Route::get('users/{user}/edit', [UsersController::class, 'edit'])
        ->name('users.edit');

    Route::put('users/{user}', [UsersController::class, 'update'])
        ->name('users.update');

    Route::delete('users/{user}', [UsersController::class, 'destroy'])
        ->name('users.destroy');

    Route::put('users/{user}/restore', [UsersController::class, 'restore'])
        ->name('users.restore');

    Route::get('messenger', [MessengerController::class, 'index'])
        ->name('messengers');

    Route::get('messenger/messages', [MessengerController::class, 'getMessages'])
        ->name('messenger.messages');

    Route::post('/messenger/send-message', [MessengerController::class, 'sendMessage'])
        ->name('messenger.send-message');

    Route::post('/messenger/send-media', [MessengerController::class, 'sendMedia'])
        ->name('messenger.send-media');

    Route::post('/messenger/send-voice', [MessengerController::class, 'sendVoice'])
        ->name('messenger.send-voice');

    Route::get('messenger/get_media/{message_id}', [MessengerController::class, 'getMedia'])
        ->name('messenger.get-media');

    // Reports

    Route::get('reports', [ReportsController::class, 'index'])
        ->name('reports');

// Images

    Route::get('/img/{path}', [ImagesController::class, 'show'])
        ->where('path', '.*')
        ->name('image');

// Settings
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name("settings")
        ->middleware("auth");

    Route::get("/settings/telegram-chat/create", [SettingsController::class, 'createTelegramChat'])
        ->name("settings.create-telegram-chat")
        ->middleware("auth");

    Route::post("/settings/send-code", [SettingsController::class, 'sendCode'])
        ->name("settings.send-code")
        ->middleware("auth");

    Route::post("/settings/verify-code", [SettingsController::class, 'verifyCode'])
        ->name("settings.verify-code")
        ->middleware("auth");

    Route::post("/settings/verify-password", [SettingsController::class, 'verifyPassword'])
        ->name("settings.verify-password")
        ->middleware("auth");

    Route::delete("/settings/delete-connection/{id}", [SettingsController::class, 'deleteConnection'])
        ->name("settings.delete")
        ->middleware("auth");

    Route::resource('employees', EmployeesController::class);

    Route::resource('bots', BotController::class);

    Route::resource('shops', ShopController::class);

    Route::resource('roles', RoleController::class);
});


Route::get('test', function () {

    Oauthapi::setOauthStorage(
        new FileStorage(['path' => public_path('auth')])
    );

    $amo = Oauthapi::setInstance([
        'domain' => config('amo.domain'),
        'client_id' => config('amo.account_id'),
        'client_secret' => config('amo.secret_key'),
        'redirect_uri' => config('amo.redirect_uri'),
    ]);
    $leads = $amo->leads();
    $account = $amo->account();
    $companies = $amo->companies();
    $first_token = $amo->fetchAccessToken('N2+k)KDf"sLbqZr');

    print_r($first_token);

    dd($amo, $leads, $account, $companies, $amo->account);
});
// Organizations

/*Route::get('organizations', [OrganizationsController::class, 'index'])
    ->name('organizations')
    ;

Route::get('organizations/create', [OrganizationsController::class, 'create'])
    ->name('organizations.create')
    ;

Route::post('organizations', [OrganizationsController::class, 'store'])
    ->name('organizations.store')
    ;

Route::get('organizations/{organization}/edit', [OrganizationsController::class, 'edit'])
    ->name('organizations.edit')
    ;

Route::put('organizations/{organization}', [OrganizationsController::class, 'update'])
    ->name('organizations.update')
    ;

Route::delete('organizations/{organization}', [OrganizationsController::class, 'destroy'])
    ->name('organizations.destroy')
    ;

Route::put('organizations/{organization}/restore', [OrganizationsController::class, 'restore'])
    ->name('organizations.restore')
    ;*/

// Messenger

//
//Route::post('messenger', [MessengerController::class, 'getDialogs'])
//    ->name('messengers.getDialogs')
//
//;

// Contacts

/*Route::get('contacts', [ContactsController::class, 'index'])
    ->name('contacts')
    ;

Route::get('contacts/create', [ContactsController::class, 'create'])
    ->name('contacts.create')
    ;

Route::post('contacts', [ContactsController::class, 'store'])
    ->name('contacts.store')
    ;

Route::get('contacts/{contact}/edit', [ContactsController::class, 'edit'])
    ->name('contacts.edit')
    ;

Route::put('contacts/{contact}', [ContactsController::class, 'update'])
    ->name('contacts.update')
    ;

Route::delete('contacts/{contact}', [ContactsController::class, 'destroy'])
    ->name('contacts.destroy')
    ;

Route::put('contacts/{contact}/restore', [ContactsController::class, 'restore'])
    ->name('contacts.restore')
    ;*/

Route::get('categories/create/{id}', [CategoryController::class, 'createShop'])->name('categories.create.shop');
Route::resource('categories', CategoryController::class)->middleware('auth');
Route::get('products/create/{id}', [ProductController::class, 'createCategory'])->name('products.create.category');
Route::resource('products', ProductController::class)->middleware('auth');

Route::post('/webhook/{slug}', [BotController::class, 'webhook'])->name('bot.webhook');
Route::get('/shop/{slug}', [BotController::class, 'shop'])->name('bot.shop');
Route::post('/basket/{slug}/store', [BotController::class, 'addBasket'])->name('basket.create')
    ->middleware('guest');
