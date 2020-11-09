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
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
            ],
            'form_params' => [
                'token' => $token
            ]
        ]);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $userObj = new User();
        $userObj->id = $user['profile']['identityId'];
        $userObj->name = $user['profile']['displayName'];
        $userObj->email = $user['profile']['emailAddress'];

        $organisations = [];
        if (isset($user['profile']['properties']['http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode'])) {
            foreach ($user['profile']['properties']['http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode'] as $regio) {
                $organisations[] = $regio;
            }
        }
        $userObj->organisations = $organisations;

        $roles = [];
        if (isset($user['roles'])) {
            foreach ($user['roles'] as $role) {
                $roles[] = $role['name'];
            }
        }
        $userObj->roles = $roles;

        return $userObj;
    }

}
