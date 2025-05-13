<?php

namespace App\Services;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use Exception;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Grant\RefreshToken;

class AmoApiService
{
    public function handle()
    {
        define('TOKEN_FILE', public_path() . '/token.json');

        $provider = new AmoCRM([
            'clientId' => config('amo.integration_id'),
            'clientSecret' => config('amo.integration_secret_key'),
            'redirectUri' => config('amo.redirect_uri'),
        ]);

        if (isset($_GET['referer'])) {
            $provider->setBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['request'])) {
            if (!isset($_GET['code'])) {
                $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
                $authorizationUrl = $provider->getAuthorizationUrl(['state' => $_SESSION['oauth2state']]);
                header('Location: ' . $authorizationUrl);
            } elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }

            /**
             * Ловим обратный код
             */
            try {
                $accessToken = $provider->getAccessToken((new AuthorizationCode), [
                    'code' => $_GET['code'],
                ]);

                if (!$accessToken->hasExpired()) {
                    $this->saveToken([
                        'accessToken' => $accessToken->getToken(),
                        'refreshToken' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                        'baseDomain' => $provider->getBaseDomain(),
                    ]);
                }
            } catch (Exception $e) {
                die((string)$e);
            }

            $ownerDetails = $provider->getResourceOwner($accessToken);

            printf('Hello, %s!', $ownerDetails->getName());
        } else {
            $accessToken = $this->getToken();

            $provider->setBaseDomain($accessToken->getValues()['baseDomain']);

            /**
             * Проверяем активен ли токен и делаем запрос или обновляем токен
             */
            if ($accessToken->hasExpired()) {
                /**
                 * Получаем токен по рефрешу
                 */
                try {
                    $accessToken = $provider->getAccessToken(new RefreshToken(), [
                        'refresh_token' => $accessToken->getRefreshToken(),
                    ]);

                    $this->saveToken([
                        'accessToken' => $accessToken->getToken(),
                        'refreshToken' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                        'baseDomain' => $provider->getBaseDomain(),
                    ]);

                } catch (Exception $e) {
                    die((string)$e);
                }
            }

            $token = $accessToken->getToken();

            try {
                /**
                 * Делаем запрос к АПИ
                 */
                $data = $provider->getHttpClient()
                    ->request('GET', $provider->urlAccount() . 'api/v2/account', [
                        'headers' => $provider->getHeaders($accessToken)
                    ]);

                $parsedBody = json_decode($data->getBody()->getContents(), true);
                printf('ID аккаунта - %s, название - %s', $parsedBody['id'], $parsedBody['name']);
            } catch (Exception $e) {
                var_dump((string)$e);
            }
        }

    }
    public function saveToken($accessToken)
    {
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];

            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getToken()
    {
        $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);

        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new \League\OAuth2\Client\Token\AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }
}
