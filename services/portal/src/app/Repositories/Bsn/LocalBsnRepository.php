<?php

declare(strict_types=1);

namespace App\Repositories\Bsn;

use App\Helpers\PostalCodeHelper;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use Carbon\CarbonInterface;

use function sprintf;
use function substr;

final class LocalBsnRepository implements BsnRepository
{
    public const PSEUDO_BSN_FIXTURE_999990007 = '0fb60aa4-1ef6-4f05-aec3-5b84b62cbcf4';
    public const LETTERS_FIXTURE_999990007 = 'AX';
    public const DATE_OF_BIRTH_FIXTURE_999990007 = '19500102';
    public const HOUSE_NUMBER_FIXTURE_999990007 = '1003';
    public const POSTAL_CODE_FIXTURE_999990007 = '9999ZA';
    public const TOKEN_FIXTURE_999990007 = 'w9fkRhGyvxXFu2Fe3Rd9C8bpkYb9C2';

    public const PSEUDO_BSN_FIXTURE_999990019 = '872c028b-f898-4b59-bf78-2944a09d5be3';

    private array $dbByBsn = [];
    private array $dbByPseudoBsnGuid = [];
    private array $dbByPersonalDetails = [];

    public function __construct()
    {
        foreach ($this->fixtures() as $fixture) {
            $this->dbByBsn[$fixture['bsn']] = $fixture;
            $this->dbByPseudoBsnGuid[$fixture['bsnGuid']] = $fixture;
            $this->dbByPersonalDetails[sprintf(
                '%s%s%s',
                $fixture['dateOfBirth'],
                PostalCodeHelper::normalize($fixture['postalCode']),
                $fixture['houseNumber'],
            )] = $fixture;
        }
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function convertBsnToPseudoBsn(string $bsn, string $accessTokenIdentifier): array
    {
        if (!isset($this->dbByBsn[$bsn])) {
            throw new BsnException('Not found');
        }

        return [$this->convertFixtureToPseudoBsn($this->dbByBsn[$bsn])];
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
        if (!isset($this->dbByBsn[$bsn])) {
            throw new BsnException('Not found');
        }

        if ($this->dbByBsn[$bsn]['dateOfBirth'] !== $dateOfBirth->format('Ymd')) {
            throw new BsnException('Not found');
        }

        return [$this->convertFixtureToPseudoBsn($this->dbByBsn[$bsn])];
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     * @throws BsnServiceException
     */
    public function getByPseudoBsnGuid(string $pseudoBsnGuid, string $accessTokenIdentifier): array
    {
        static $requestCounter = 0;

        switch ($pseudoBsnGuid) {
            case '06A6B91C-D59B-401E-A5BF-4BF9262D85F8':
                throw new BsnServiceException('Service not available (mocked)');
            case '8027C102-93EF-4735-AB66-97AA63B836EB':
                if ($requestCounter > 0) {
                    throw new BsnServiceException('Service not available: 2nd request (mocked)');
                }
                $requestCounter++;
        }

        if (!isset($this->dbByPseudoBsnGuid[$pseudoBsnGuid])) {
            throw new BsnException('Not found');
        }

        return [$this->convertFixtureToPseudoBsn($this->dbByPseudoBsnGuid[$pseudoBsnGuid])];
    }

    /**
     * @return array<PseudoBsn>
     *
     * @throws BsnException
     */
    public function lookupPseudoBsn(PseudoBsnLookup $lookup, string $accessTokenIdentifier): array
    {
        $key = sprintf(
            '%s%s%s',
            $lookup->dateOfBirth->format('Ymd'),
            PostalCodeHelper::normalize($lookup->postalCode),
            $lookup->houseNumber,
        );

        if (!isset($this->dbByPersonalDetails[$key])) {
            throw new BsnException('Not found');
        }

        return [$this->convertFixtureToPseudoBsn($this->dbByPersonalDetails[$key])];
    }

    public function getExchangeToken(string $pseudoBsnGuid, string $accessTokenIdentifier): string
    {
        if (!isset($this->dbByPseudoBsnGuid[$pseudoBsnGuid])) {
            throw new BsnException('Not found');
        }

        return $this->dbByPseudoBsnGuid[$pseudoBsnGuid]['token'];
    }

    private function convertFixtureToPseudoBsn(array $fixture): PseudoBsn
    {
        return new PseudoBsn(
            $fixture['bsnGuid'],
            '******' . substr($fixture['bsn'], -3),
            $fixture['letters'],
            $fixture['token'] ?? null,
        );
    }

    /**
     * @return array<int, array{bsn: string, bsnGuid: string, letters: string, dateOfBirth: string, houseNumber: string, postalCode: string}>
     */
    private function fixtures(): array
    {
        return [
            [
                'bsn' => '999998286',
                'bsnGuid' => '1eaf0d45-1124-4799-931d-58f628635079',
                'letters' => 'AA',
                'dateOfBirth' => '19500101',
                'houseNumber' => '01',
                'postalCode' => '9999XX',
            ],
            [
                'bsn' => '999998298',
                'bsnGuid' => '62a655cb-064f-4d27-b995-52c50771cb57',
                'letters' => 'AB',
                'dateOfBirth' => '19500102',
                'houseNumber' => '02',
                'postalCode' => '9999XX',
            ],
            [
                'bsn' => '999998304',
                'bsnGuid' => '53k655cb-064f-4d27-b995-52c50771hi12',
                'letters' => 'AC',
                'dateOfBirth' => '19500103',
                'houseNumber' => '03',
                'postalCode' => '9999XX',
            ],
            [
                'bsn' => '999998316',
                'bsnGuid' => '52k655cb-064f-4d27-b995-52c50771hi14',
                'letters' => 'AC',
                'dateOfBirth' => '19500103',
                'houseNumber' => '03',
                'postalCode' => '9999XX',
            ],
            [
                'bsn' => '999998328',
                'bsnGuid' => '2eaf0d45-1124-4799-931d-58f628635079',
                'letters' => 'AA',
                'dateOfBirth' => '19500101',
                'houseNumber' => '04',
                'postalCode' => '9999XY',
            ],
            [
                'bsn' => '999998341',
                'bsnGuid' => '32a655cb-064f-4d27-b995-52c50771cb57',
                'letters' => 'AB',
                'dateOfBirth' => '19700105',
                'houseNumber' => '05',
                'postalCode' => '9999XZ',
            ],
            [
                'bsn' => '999998353',
                'bsnGuid' => '43k655cb-064f-4d27-b995-52c50771hi12',
                'letters' => 'AC',
                'dateOfBirth' => '19800106',
                'houseNumber' => '06',
                'postalCode' => '9999XU',
            ],
            [
                'bsn' => '999998365',
                'bsnGuid' => '62k655cb-064f-4d27-b995-52c50771hi14',
                'letters' => 'AC',
                'dateOfBirth' => '20200107',
                'houseNumber' => '07',
                'postalCode' => '9999XW',
            ],
            [
                'bsn' => '999998377',
                'bsnGuid' => '1eaf0d45-1124-4799-931d-58f62863507',
                'letters' => 'AB',
                'dateOfBirth' => '20000101',
                'houseNumber' => '01',
                'postalCode' => '9999XV',
            ],
            [
                'bsn' => '999998377',
                'bsnGuid' => '1eaf0d45-1124-4799-931d-58f62863508',
                'letters' => 'AB',
                'dateOfBirth' => '20000101',
                'houseNumber' => '01',
                'postalCode' => '9999XV',
            ],
            // Simulate Bsn service failure on all requests
            [
                'bsn' => '999998389',
                'bsnGuid' => '06A6B91C-D59B-401E-A5BF-4BF9262D85F8',
                'letters' => 'BB',
                'dateOfBirth' => '19500102',
                'houseNumber' => '2',
                'postalCode' => '1041GI',
            ],
            // Simulate Bsn service failure on 2nd request
            [
                'bsn' => '999998401',
                'bsnGuid' => '8027C102-93EF-4735-AB66-97AA63B836EB',
                'letters' => 'BC',
                'dateOfBirth' => '19500103',
                'houseNumber' => '3',
                'postalCode' => '1041GJ',
            ],

            // webportal mittens
            [
                'bsn' => '999999205',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e40',
                'letters' => 'K',
                'dateOfBirth' => '19500102',
                'houseNumber' => '1',
                'postalCode' => '3405GH',
            ],
            [
                'bsn' => '555555379',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e41',
                'letters' => 'J',
                'dateOfBirth' => '19520602',
                'houseNumber' => '1',
                'postalCode' => '33344GH',
            ],
            [
                'bsn' => '555555719',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e42',
                'letters' => 'H',
                'dateOfBirth' => '19500102',
                'houseNumber' => '1',
                'postalCode' => '4698GK',
            ],
            [
                'bsn' => '555555914',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e43',
                'letters' => 'J',
                'dateOfBirth' => '19720320',
                'houseNumber' => '1',
                'postalCode' => '5091LK',
            ],
            [
                'bsn' => '999999217',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e44',
                'letters' => 'P',
                'dateOfBirth' => '19900122',
                'houseNumber' => '1',
                'postalCode' => '6574DF',
            ],
            [
                'bsn' => '999999229',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e45',
                'letters' => 'P',
                'dateOfBirth' => '19600803',
                'houseNumber' => '1',
                'postalCode' => '6627TT',
            ],
            [
                'bsn' => '999999801',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e46',
                'letters' => 'A',
                'dateOfBirth' => '19500102',
                'houseNumber' => '1',
                'postalCode' => '7225PH',
            ],
            [
                'bsn' => '999999813',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e47',
                'letters' => 'B',
                'dateOfBirth' => '20030303',
                'houseNumber' => '1',
                'postalCode' => '7779GL',
            ],
            [
                'bsn' => '999999825',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e48',
                'letters' => 'B',
                'dateOfBirth' => '19800403',
                'houseNumber' => '1',
                'postalCode' => '9087FD',
            ],
            [
                'bsn' => '999999837',
                'bsnGuid' => 'b6b2cb10-ecef-4ff3-892a-09c503066e49',
                'letters' => 'BA',
                'dateOfBirth' => '19500102',
                'houseNumber' => '1',
                'postalCode' => '1041GH',
            ],
            [
                'bsn' => '999990007',
                'bsnGuid' => self::PSEUDO_BSN_FIXTURE_999990007,
                'letters' => self::LETTERS_FIXTURE_999990007,
                'dateOfBirth' => self::DATE_OF_BIRTH_FIXTURE_999990007,
                'houseNumber' => self::HOUSE_NUMBER_FIXTURE_999990007,
                'postalCode' => self::POSTAL_CODE_FIXTURE_999990007,
                'token' => self::TOKEN_FIXTURE_999990007,
            ],
            [
                'bsn' => '999990019',
                'bsnGuid' => self::PSEUDO_BSN_FIXTURE_999990019,
                'letters' => 'AB',
                'dateOfBirth' => '19500202',
                'houseNumber' => '1000',
                'postalCode' => '9999XA',
                'token' => 'b10a8db164e0754105b7a99be72e3fe5',
            ],
            [
                'bsn' => '999990020',
                'bsnGuid' => '022f315d-1725-4b32-a0a9-b139965ddc71',
                'letters' => 'CA',
                'dateOfBirth' => '19500302',
                'houseNumber' => '1001',
                'postalCode' => '9999XX',
                'token' => 'e8e6c9598eaa04fd020976e549f27e0a',
            ],
            [
                'bsn' => '999990032',
                'bsnGuid' => 'd6ee5096-6534-4121-a1e2-0b41618a7e23',
                'letters' => 'CB',
                'dateOfBirth' => '19500402',
                'houseNumber' => '1002',
                'postalCode' => '9999ZZ',
                'token' => 'f5a5d0a79ea48a589000f8218491356d',
            ],
            [
                'bsn' => '999990044',
                'bsnGuid' => '20fa6a1d-b33c-49a2-9ae1-1f2dda2b64fc',
                'letters' => 'GA',
                'dateOfBirth' => '19500502',
                'houseNumber' => '1003',
                'postalCode' => '9999ZA',
                'token' => 'dc7180c281f83015b6be2fa227ffe255',
            ],
            [
                'bsn' => '999990056',
                'bsnGuid' => '61721c39-e526-4e2d-aa22-c9bb74b12c70',
                'letters' => 'GB',
                'dateOfBirth' => '19500602',
                'houseNumber' => '1000',
                'postalCode' => '9999XA',
                'token' => '864f6dd1b331375fd980d2b9a00d8ebd',
            ],
            [
                'bsn' => '999990068',
                'bsnGuid' => '935dd4c6-497b-43c5-9ece-602f0e0c1428',
                'letters' => 'GC',
                'dateOfBirth' => '19500702',
                'houseNumber' => '1001',
                'postalCode' => '9999XX',
                'token' => 'fdda75f2eda7f734bfcc24008b147c64',
            ],
            [
                'bsn' => '999990081',
                'bsnGuid' => '9ec98e57-f42d-495b-9585-24eac7c3274d',
                'letters' => 'GD',
                'dateOfBirth' => '19500802',
                'houseNumber' => '1002',
                'postalCode' => '9999ZZ',
                'token' => '4bbf369bdf75137f1b375cf40b91df51',
            ],
            [
                'bsn' => '999990093',
                'bsnGuid' => '25406f37-6e2a-4348-9943-b26ebdba4d40',
                'letters' => 'GE',
                'dateOfBirth' => '19500902',
                'houseNumber' => '1003',
                'postalCode' => '9999ZA',
                'token' => '781c5d785c19afa316e72f25f2ec79d2',
            ],
            [
                'bsn' => '999990111',
                'bsnGuid' => '206afdb5-d3ad-4d75-aeba-c51d3190165d',
                'letters' => 'LA',
                'dateOfBirth' => '19500103',
                'houseNumber' => '1000',
                'postalCode' => '9999XA',
                'token' => '0562dd7c8064b18895b77947b8007362',
            ],
        ];
    }
}
