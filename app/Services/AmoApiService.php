<?php

namespace App\Services;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Models\AmoConnection;
use App\Models\User;
use Arr;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;
use Log;

class AmoApiService
{
    protected AmoCRM $provider;
    protected User|null $user;

    public function __construct(User $user)
    {
        $this->user = auth()->user() ?? $user;
        Log::debug('this user: ' . json_encode([$this->user, $user]));
        $this->provider = new AmoCRM([
            'clientId' => config('amo.integration_id'),
            'clientSecret' => config('amo.integration_secret_key'),
            'redirectUri' => config('amo.redirect_uri'),
        ]);
    }

    /**
     * @return RedirectResponse|void
     * @throws GuzzleException
     */
    public function handle()
    {
        if (isset($_GET['referer'])) {
            $this->provider->setBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['request'])) {

            if (!isset($_GET['code'])) {
                header('Location: ' . $this->provider->getAuthorizationUrl(['state' => Str::random()]));
            }

            try {
                $accessToken = $this->provider->getAccessToken((new AuthorizationCode), ['code' => $_GET['code']]);

                if (!$accessToken->hasExpired()) {
                    $this->saveToken($accessToken);
                }
            } catch (Exception $e) {
                die((string)$e);
            }

        }
        return redirect()->route('employees.index')->with('success', 'Подключите ползователя AmoCRM');
    }

    public function saveToken($accessToken)
    {
        $token = [
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
            'base_domain' => $this->provider->getBaseDomain(),
        ];

        if (Arr::has($token, ['access_token', 'refresh_token', 'expires', 'base_domain'])) {

            $token['account_id'] = $this->user->account_id;

            Log::debug('updateOrCreate AmoCRM token: ' . json_encode($token));

            return AmoConnection::query()->updateOrCreate(['base_domain' => $token['base_domain']], $token);

        }

        exit('Invalid access token ' . var_export($token, true));
    }

    /**
     * @throws GuzzleException
     */
    public function getToken(): AccessToken|string
    {
        $token = auth()->user()->account->amoConnections->firstOrFail();
        Log::debug('AmoCRM token: ' . var_export($token, true));

        if (!$token) {
            exit('Invalid access token ' . var_export($token, true));
        }

        $token = new AccessToken($token->toArray());
        Log::debug('AmoCRM token values: ' . var_export($token->getValues(), true));
        $this->provider->setBaseDomain($token->getValues()['base_domain']);

        if ($token->hasExpired()) {

            // get a token for a refresh
            try {
                Log::debug('AmoCRM token expired: ' . var_export($token, true));
                $token = $this->provider->getAccessToken(new RefreshToken(), [
                    'refresh_token' => $token->getRefreshToken()
                ]);

                $this->saveToken($token);
                Log::debug('AmoCRM token saved: ' . var_export($token->getValues(), true));

            } catch (Exception $e) {
                die((string)$e);
            }
        }
        return $token;
    }

    /**
     * @throws GuzzleException
     * @throws ConnectionException
     */
    public function getAmoAccount()
    {
        $accessToken = $this->getToken();
        Log::debug('AmoCRM account: ' . var_export($accessToken, true));
        $values = $accessToken->getValues();
        $baseDomain = Arr::get($values, 'base_domain');
        $url = "https://$baseDomain/api/v4/users?with=amojo_id";

        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $accessToken->getToken(),])->get($url);
        Log::debug('AmoCRM response: ' . $response->body());

        return Arr::get($response->json(), '_embedded.users', []);
    }
}
