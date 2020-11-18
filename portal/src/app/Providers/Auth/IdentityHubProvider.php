<?php

namespace App\Providers\Auth;

use Illuminate\Http\Request;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class IdentityHubProvider extends AbstractProvider implements ProviderInterface
{
    private $config = [];

    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);
        $this->config = config('services.identityhub');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->config['authUrl'], $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->config['tokenUrl'];
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
        $response = $this->getHttpClient()->post($this->config['userUrl'], [
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
        $claim = $this->config['organisationClaim'];
        $organisations = [];
        if (isset($user['profile']['properties'][$claim])) {
            foreach ($user['profile']['properties'][$claim] as $regio) {
                $organisations[] = $regio;
            }
        }
        $userObj->organisations = $organisations;
        $roles = config('authorization.roles');
        $userRoles = [];
        if (isset($user['roles'])) {
            foreach ($user['roles'] as $role) {
                $matchedRole = array_search($role['name'], $roles);
                if ($matchedRole) {
                    $userRoles[] = $role;
                }
            }
        }
        $userObj->roles = $userRoles;

        return $userObj;
    }

}
