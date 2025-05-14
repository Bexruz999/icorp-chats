<?php

namespace App\Services;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Models\AmoToken;
use Arr;
use Exception;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;

class AmoApiService
{
    public function handle(): void
    {
        $provider = new AmoCRM([
            'clientId' => config('amo.integration_id'),
            'clientSecret' => config('amo.integration_secret_key'),
            'redirectUri' => config('amo.redirect_uri'),
        ]);

        if (isset($_GET['referer'])) {
            $provider->setBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['request'])) {

            try {
                $accessToken = $provider->getAccessToken((new AuthorizationCode), ['code' => $_GET['code']]);

                if (!$accessToken->hasExpired()) {
                    $this->saveToken([
                        'token' => $accessToken->getToken(),
                        'refresh_token' => $accessToken->getRefreshToken(),
                        'expires_at' => $accessToken->getExpires(),
                        'base_domain' => $provider->getBaseDomain(),
                    ]);
                }
            } catch (Exception $e) {
                die((string)$e);
            }
        } else {
            $accessToken = $this->getToken();

            $provider->setBaseDomain($accessToken->getValues()['base_domain']);

            /**
             * check if the token is active and make a request or update the token.
             */
            if ($accessToken->hasExpired()) {
                /**
                 * get a token for a refresh
                 */
                try {
                    $accessToken = $provider->getAccessToken(new RefreshToken(), [
                        'refresh_token' => $accessToken->getRefreshToken(),
                    ]);

                    $this->saveToken([
                        'token' => $accessToken->getToken(),
                        'refresh_token' => $accessToken->getRefreshToken(),
                        'expires_at' => $accessToken->getExpires(),
                        'base_domain' => $provider->getBaseDomain(),
                    ]);

                } catch (Exception $e) {
                    die((string)$e);
                }
            }
        }

    }

    public function saveToken($token)
    {
        if (Arr::has($token, ['token', 'refresh_token', 'expires_at', 'base_domain'])) {

            $token['account_id'] = auth()->user()->account_id;

            return AmoToken::query()->updateOrCreate([$token['token']], $token);
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

        if (Arr::has($token, ['token', 'refresh_token', 'expires_at', 'base_domain'])) {
            return new AccessToken($token);
        } else {
            exit('Invalid access token ' . var_export($token, true));
        }
    }
}
