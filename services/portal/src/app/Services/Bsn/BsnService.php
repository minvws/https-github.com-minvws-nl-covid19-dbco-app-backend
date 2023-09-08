<?php

declare(strict_types=1);

namespace App\Services\Bsn;

use App\Exceptions\IdentifiedBsnNotValidAnymoreException;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\BsnServiceException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\ItemNotFoundException;

use function collect;
use function count;
use function in_array;
use function preg_match;
use function substr;

class BsnService
{
    public function __construct(
        private readonly BsnRepository $bsnRepository,
    ) {
    }

    /**
     * @throws BsnException
     */
    public function convertBsnAndDateOfBirthToPseudoBsn(
        string $bsn,
        CarbonInterface $dateOfBirth,
        string $organisationExternalId,
    ): PseudoBsn {
        $pseudoBsnCollection = $this->bsnRepository->convertBsnAndDateOfBirthToPseudoBsn($bsn, $dateOfBirth, $organisationExternalId);

        return $this->getPseudoBsnFromCollection($pseudoBsnCollection);
    }

    /**
     * @throws BsnException
     * @throws BsnServiceException
     */
    public function getByPseudoBsnGuid(string $pseudoBsnGuid, string $organisationExternalId): PseudoBsn
    {
        $pseudoBsnCollection = $this->bsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, $organisationExternalId);

        return $this->getPseudoBsnFromCollection($pseudoBsnCollection);
    }

    /**
     * @throws BsnException
     * @throws IdentifiedBsnNotValidAnymoreException
     */
    public function newExchangeTokenForIdentifiedPseudoBsn(
        PseudoBsnLookup $pseudoBsnLookup,
        string $identifiedPseudoBsn,
        string $organisationExternalId,
    ): string {
        $pseudoBsnLookupResult = $this->bsnRepository->lookupPseudoBsn($pseudoBsnLookup, $organisationExternalId);

        if (!$this->isIdentifiedPseudoBsnFound($pseudoBsnLookupResult, $identifiedPseudoBsn)) {
            throw new IdentifiedBsnNotValidAnymoreException();
        }

        return $this->bsnRepository->getExchangeToken($identifiedPseudoBsn, $organisationExternalId);
    }

    /**
     * @throws BsnException
     */
    public function pseudoBsnLookupWithLastThreeDigits(
        string $lastThreeDigits,
        DateTimeImmutable $dateOfBirth,
        string $postalCode,
        string $houseNumber,
        ?string $houseNumberSuffix,
        string $organisationExternalId,
    ): PseudoBsn {
        $pseudoBsnLookupResponses = $this->findPseudoBsn(
            $dateOfBirth,
            $postalCode,
            $houseNumber,
            $houseNumberSuffix,
            $organisationExternalId,
        );

        return $this->parseMatchingBsn(collect($pseudoBsnLookupResponses), $lastThreeDigits);
    }

    /**
     * @throws BsnException
     */
    public function pseudoBsnLookupWithBsn(
        string $bsn,
        DateTimeImmutable $dateOfBirth,
        string $postalCode,
        string $houseNumber,
        ?string $houseNumberSuffix,
        string $organisationExternalId,
    ): PseudoBsn {
        $pseudoBsnLookupResponses = $this->findPseudoBsn(
            $dateOfBirth,
            $postalCode,
            $houseNumber,
            $houseNumberSuffix,
            $organisationExternalId,
        );

        $pseudoBsnConvertResponse = $this->convertBsnToPseudoBsn($bsn, $organisationExternalId);

        // phpcs:disable SlevomatCodingStandard.Functions.StrictCall
        if (!in_array($pseudoBsnConvertResponse, $pseudoBsnLookupResponses, false)) {
            throw new BsnException('given bsn does not match any from lookup');
        }

        return $pseudoBsnConvertResponse;
    }

    /**
     * @throws BsnException
     */
    private function convertBsnToPseudoBsn(string $bsn, string $organisationExternalId): PseudoBsn
    {
        $pseudoBsnCollection = $this->bsnRepository->convertBsnToPseudoBsn($bsn, $organisationExternalId);

        return $this->getPseudoBsnFromCollection($pseudoBsnCollection);
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    private function findPseudoBsn(
        DateTimeImmutable $dateOfBirth,
        string $postalCode,
        string $houseNumber,
        ?string $houseNumberSuffix,
        string $organisationExternalId,
    ): array {
        $collection = $this->pseudoBsnRepositoryLookup(
            $dateOfBirth,
            $postalCode,
            $houseNumber,
            $houseNumberSuffix,
            $organisationExternalId,
        );

        // If there is data within the collection or the suffix is null, then we can early return
        if (count($collection) !== 0 || $houseNumberSuffix === null) {
            return $collection;
        }

        $houseNumberSuffixStripped = $this->stripHouseNumberSuffix($houseNumberSuffix);

        $collection = $this->pseudoBsnRepositoryLookup(
            $dateOfBirth,
            $postalCode,
            $houseNumber,
            $houseNumberSuffixStripped,
            $organisationExternalId,
        );

        // See if the collection now has any records or if the houseNumberSuffixStripped is null, if so we will return the collection early
        if (count($collection) !== 0 || $houseNumberSuffixStripped === null) {
            return $collection;
        }

        // If everything else fails, we do a last call without a houseNumberSuffix and return it directly
        return $this->pseudoBsnRepositoryLookup($dateOfBirth, $postalCode, $houseNumber, null, $organisationExternalId);
    }

    private function stripHouseNumberSuffix(string $houseNumberSuffix): ?string
    {
        if (!preg_match('/[a-z0-9]/i', $houseNumberSuffix, $match)) {
            return null;
        }

        return substr($match[0], 0, 1);
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    private function pseudoBsnRepositoryLookup(
        DateTimeImmutable $dateOfBirth,
        string $postalCode,
        string $houseNumber,
        ?string $houseNumberSuffix,
        string $organisationExternalId,
    ): array {
        $lookup = new PseudoBsnLookup($dateOfBirth, $postalCode, $houseNumber, $houseNumberSuffix);
        return $this->bsnRepository->lookupPseudoBsn($lookup, $organisationExternalId);
    }

    /**
     * @param Collection<int, PseudoBsn> $pseudoBsns
     *
     * @throws BsnException
     */
    private function parseMatchingBsn(Collection $pseudoBsns, string $lastThreeDigits): PseudoBsn
    {
        if ($pseudoBsns->isEmpty()) {
            throw new BsnException('No matching results found');
        }

        if ($pseudoBsns->count() > 1) {
            throw new BsnException('Too many matching results found');
        }

        $pseudoBsns = $pseudoBsns->filter(static function (PseudoBsn $bsn) use ($lastThreeDigits): bool {
            return substr($bsn->getCensoredBsn(), -3) === $lastThreeDigits;
        });

        try {
            return $pseudoBsns->firstOrFail();
        } catch (ItemNotFoundException) {
            throw new BsnException('No matching results found');
        }
    }

    /**
     * @throws BsnException
     */
    private function getPseudoBsnFromCollection(array $pseudoBsnCollection): PseudoBsn
    {
        if (count($pseudoBsnCollection) === 0) {
            throw new BsnException('failed converting bsn to pseudo bsn: none returned');
        }

        if (count($pseudoBsnCollection) > 1) {
            throw new BsnException('failed converting bsn to pseudo bsn: multiple results found');
        }

        return $pseudoBsnCollection[0];
    }

    private function isIdentifiedPseudoBsnFound(array $pseudoBsns, string $identifiedPseudoBsn): bool
    {
        foreach ($pseudoBsns as $pseudoBsn) {
            if ($pseudoBsn->getGuid() === $identifiedPseudoBsn) {
                return true;
            }
        }

        return false;
    }
}
