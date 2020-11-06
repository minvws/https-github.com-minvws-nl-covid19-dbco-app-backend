<?php

namespace App\Providers\Auth;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class IdentityHubProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.ggdghor.nl/ggdghornl/oauth2/v1/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://login.ggdghor.nl/ggdghornl/oauth2/v1/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)],
            'body'    => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post('https://login.ggdghor.nl/ggdghornl/oauth2/v1/introspect', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['unique_name'],
            'nickname' => $user['nickname'],
            'name'     => $user['name'],
        ]);
    }

}
