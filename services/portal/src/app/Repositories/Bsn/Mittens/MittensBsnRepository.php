<?php

declare(strict_types=1);

namespace App\Repositories\Bsn\Mittens;

use App\Helpers\PostalCodeHelper;
use App\Http\Client\Guzzle\MittensClientException;
use App\Http\Client\Guzzle\MittensClientInterface;
use App\Http\Requests\Mittens\MittensRequest;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use Carbon\CarbonInterface;

use function array_key_exists;
use function property_exists;

final class MittensBsnRepository implements BsnRepository
{
    private const SERVICE_VIA_PII = '/service/via_pii/';
    private const SERVICE_VIA_UUID = '/service/via_uuid/';
    private const SERVICE_GET_EXCHANGE_TOKEN = '/service/get_exchange_token';
    private const SERVICE_VIA_DIGID = '/service/via_digid/';

    public function __construct(
        private readonly MittensClientInterface $mittensClient,
        private readonly array $digidAccessTokens,
        private readonly array $piiAccessTokens,
        private readonly array $tokensFor = [],
    ) {
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function convertBsnToPseudoBsn(string $bsn, string $accessTokenIdentifier): array
    {
        $response = $this->post(
            self::SERVICE_VIA_DIGID,
            $this->convertToDigidRequestBody($bsn, $accessTokenIdentifier),
        );

        return $this->convertToPseudoBsnCollection($response);
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function convertBsnAndDateOfBirthToPseudoBsn(
        string $bsn,
        CarbonInterface $dateOfBirth,
        string $accessTokenIdentifier,
    ): array {
        $response = $this->post(
            self::SERVICE_VIA_DIGID,
            $this->convertToDigidRequestBody($bsn, $accessTokenIdentifier, $dateOfBirth),
        );

        return $this->convertToPseudoBsnCollection($response);
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function getByPseudoBsnGuid(string $pseudoBsnGuid, string $accessTokenIdentifier): array
    {
        $response = $this->post(
            self::SERVICE_VIA_UUID,
            $this->convertToUuidRequestBody($pseudoBsnGuid, $accessTokenIdentifier),
        );

        return $this->convertToPseudoBsnCollection($response);
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function lookupPseudoBsn(PseudoBsnLookup $lookup, string $accessTokenIdentifier): array
    {
        $response = $this->post(
            self::SERVICE_VIA_PII,
            $this->convertToPiiRequestBody($lookup, $accessTokenIdentifier),
        );

        return $this->convertToPseudoBsnCollection($response);
    }

    /**
     * @throws BsnException
     */
    public function getExchangeToken(string $pseudoBsnGuid, string $accessTokenIdentifier): string
    {
        $response = $this->post(
            self::SERVICE_GET_EXCHANGE_TOKEN,
            $this->convertToExchangeRequestBody($pseudoBsnGuid, $accessTokenIdentifier),
        );

        if (!property_exists($response, 'token')) {
            throw new BsnException('no token in response');
        }

        return $response->token;
    }

    /**
     * @throws BsnException
     */
    private function convertToDigidRequestBody(
        string $bsn,
        string $accessTokenIdentifier,
        ?CarbonInterface $dateOfBirth = null,
    ): array {
        $requestBody = [
            'digid_access_token' => $this->getAccessToken($this->digidAccessTokens, $accessTokenIdentifier),
            'BSN' => $bsn,
        ];

        if ($dateOfBirth !== null) {
            $requestBody['birthdate'] = $dateOfBirth->format('Ymd');
        }

        return $requestBody;
    }

    /**
     * @throws BsnException
     */
    private function convertToExchangeRequestBody(string $pseudoBsn, string $accessTokenIdentifier): array
    {
        return [
            'access_token' => $this->getAccessToken($this->piiAccessTokens, $accessTokenIdentifier),
            'guid' => $pseudoBsn,
            'target_provider' => 'secure_mail',
        ];
    }

    /**
     * @throws BsnException
     */
    private function convertToPiiRequestBody(PseudoBsnLookup $request, string $accessTokenIdentifier): array
    {
        $requestBody = [
            'access_token' => $this->getAccessToken($this->piiAccessTokens, $accessTokenIdentifier),
            'birthdate' => $request->dateOfBirth->format('Ymd'),
            'zipcode' => PostalCodeHelper::normalize($request->postalCode),
            'house_number' => $request->houseNumber,
        ];

        if ($this->tokensFor) {
            $requestBody['tokens_for'] = $this->tokensFor;
        }
        if ($request->houseNumberSuffix !== null) {
            $requestBody['house_letter'] = $request->houseNumberSuffix;
        }

        return $requestBody;
    }

    /**
     * @throws BsnException
     */
    private function convertToUuidRequestBody(string $pseudoBsnGuid, string $accessTokenIdentifier): array
    {
        return [
            'access_token' => $this->getAccessToken($this->piiAccessTokens, $accessTokenIdentifier),
            'UUID' => $pseudoBsnGuid,
        ];
    }

    /**
     * @throws BsnException
     */
    private function getAccessToken(array $accessTokens, string $accessTokenIdentifier): string
    {
        if (!array_key_exists($accessTokenIdentifier, $accessTokens)) {
            throw new BsnException('unknown access token identifier given');
        }

        return $accessTokens[$accessTokenIdentifier];
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    private function convertToPseudoBsnCollection(object $response): array
    {
        if (!property_exists($response, 'data')) {
            throw new BsnException('no data-field in response');
        }

        $pseudoBsnCollection = [];
        foreach ($response->data as $pseudoBsnData) {
            $pseudoBsnCollection[] = new PseudoBsn($pseudoBsnData->guid, $pseudoBsnData->censored_bsn, $pseudoBsnData->letters);
        }

        return $pseudoBsnCollection;
    }

    /**
     * @throws BsnException
     */
    private function post(string $uri, array $body): object
    {
        try {
            return $this->mittensClient->post(new MittensRequest($uri, $body));
        } catch (MittensClientException $mittensClientException) {
            throw BsnException::fromThrowable($mittensClientException);
        }
    }
}
