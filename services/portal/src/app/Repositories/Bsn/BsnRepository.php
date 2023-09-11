<?php

declare(strict_types=1);

namespace App\Repositories\Bsn;

use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use Carbon\CarbonInterface;

interface BsnRepository
{
    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function convertBsnToPseudoBsn(string $bsn, string $accessTokenIdentifier): array;

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function convertBsnAndDateOfBirthToPseudoBsn(
        string $bsn,
        CarbonInterface $dateOfBirth,
        string $accessTokenIdentifier,
    ): array;

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     * @throws BsnServiceException
     */
    public function getByPseudoBsnGuid(string $pseudoBsnGuid, string $accessTokenIdentifier): array;

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function lookupPseudoBsn(PseudoBsnLookup $lookup, string $accessTokenIdentifier): array;

    /**
     * @throws BsnException
     */
    public function getExchangeToken(string $pseudoBsnGuid, string $accessTokenIdentifier): string;
}
