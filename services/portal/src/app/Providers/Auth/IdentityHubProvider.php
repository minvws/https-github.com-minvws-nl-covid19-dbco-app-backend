<?php

declare(strict_types=1);

namespace App\Providers\Auth;

use App\Dto\Auth\IdentityHubProviderConfigDto;
use App\Helpers\Config;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function base64_encode;
use function is_array;
use function json_decode;

class IdentityHubProvider extends AbstractProvider implements ProviderInterface
{
    private IdentityHubProviderConfigDto $configDto;

    /**
     * @inheritdoc
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [])
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);

        $this->configDto = IdentityHubProviderConfigDto::fromConfig();
    }

    public function logout(): void
    {
        $identityHubSessionData = Session::get('identityHub');
        if (!is_array($identityHubSessionData) || !array_key_exists('accessToken', $identityHubSessionData)) {
            return;
        }
        $token = $identityHubSessionData['accessToken'];

        $this->getHttpClient()->post($this->getRevokeUrl(), [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'form_params' => [
                'token' => $token,
                'token_type_hint' => 'access_token',
                'client_id' => $this->clientId,
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->configDto->authUrl, $state);
    }

    protected function getRevokeUrl(): string
    {
        return $this->configDto->revokeUrl;
    }

    protected function getTokenUrl(): string
    {
        return $this->configDto->tokenUrl;
    }

    /**
     * @inheritdoc
     */
    public function getAccessTokenResponse($code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        $responseBody = json_decode($response->getBody()->getContents(), true);
        Assert::isArray($responseBody);
        $accessToken = Arr::get($responseBody, 'access_token');
        Session::put('identityHub', ['accessToken' => $accessToken]);

        return $responseBody;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenFields($code): array
    {
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';
        return $fields;
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->post($this->configDto->userUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ],
            'form_params' => [
                'token' => $token,
            ],
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        Assert::isArray($result);

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @throws SessionExpiredException
     */
    protected function mapUserToObject(array $user): IdentityHubUser
    {
        $lastLogin = CarbonImmutable::createFromFormat('Y-m-d\TH:i:s.v\Z', $user['profile']['lastLogin']);
        if ($lastLogin === false) {
            throw new SessionExpiredException('unable to retrieve lastLogin date');
        }
        if ($lastLogin->lessThan(CarbonImmutable::now()->subMinutes(Config::integer('session.lifetime')))) {
            throw new SessionExpiredException('session expired');
        }

        $departmentClaim = $this->configDto->claimsDepartment;
        $vrRegioCodeClaim = $this->configDto->claimsVrRegioCode;

        $userObj = new IdentityHubUser();
        $userObj->id = $user['profile']['identityId'];
        $userObj->name = $user['profile']['displayName'];
        $userObj->email = $user['profile']['emailAddress'];
        if (array_key_exists($departmentClaim, $user['profile']['properties'])) {
            $userObj->departments = $user['profile']['properties'][$departmentClaim];
        }
        if (array_key_exists($vrRegioCodeClaim, $user['profile']['properties'])) {
            $userObj->organisations = $user['profile']['properties'][$vrRegioCodeClaim];
        }
        if (array_key_exists('roles', $user)) {
            $userObj->roles = $user['roles'];
        }

        return $userObj;
    }
}
