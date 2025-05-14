<?php

namespace App\Services;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Models\AmoToken;
use Arr;
use Exception;
use Illuminate\Support\Str;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;

class AmoApiService
{
    protected AmoCRM $provider;

    public function __construct()
    {
        $this->provider = new AmoCRM([
            'clientId' => config('amo.integration_id'),
            'clientSecret' => config('amo.integration_secret_key'),
            'redirectUri' => config('amo.redirect_uri'),
        ]);
    }

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
        return redirect()->route('settings');
    }

    public function saveToken($accessToken)
    {
        $token = [
            'token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires_at' => $accessToken->getExpires(),
            'base_domain' => $this->provider->getBaseDomain(),
        ];

        if (Arr::has($token, ['token', 'refresh_token', 'expires_at', 'base_domain'])) {

            $token['account_id'] = auth()->user()->account_id;

            return AmoToken::query()->updateOrCreate(['token' => $token['token']], $token);

        } else {
            exit('Invalid access token ' . var_export($token, true));
        }
    }

    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        $token = AmoToken::query()->where('account_id', auth()->user()->account_id)->latest();

        $token = new AccessToken($token);
        $this->provider->setBaseDomain($token->getValues()['base_domain']);

        if ($token->hasExpired()) {
            /**
             * get a token for a refresh
             */
            try {
                $token = $this->provider->getAccessToken(new RefreshToken(), [
                    'refresh_token' => $token->getRefreshToken()
                ]);

                $this->saveToken($token);

            } catch (Exception $e) {
                die((string)$e);
            }
        }
        return $token->getToken();
    }
}
