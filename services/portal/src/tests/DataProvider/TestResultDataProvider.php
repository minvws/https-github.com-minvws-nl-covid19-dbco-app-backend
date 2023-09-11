<?php

declare(strict_types=1);

namespace Tests\DataProvider;

use Carbon\CarbonImmutable;

final class TestResultDataProvider
{
    public static function payload(string $ggdIdentifier = '00001'): array
    {
        return [
            'orderId' => '000A0000000',
            'messageId' => '500000012',
            'senderRegion' => null,
            'hpzoneNumber' => '0ccf7efe-6e32-46ba-9894-b9079923b82f',
            'ggdIdentifier' => $ggdIdentifier,
            'person' => [
                'initials' => 'A.',
                'firstName' => 'Henk',
                'surname' => 'Jong',
                'bsn' => '999998286',
                'vNumber' => null,
                'dateOfBirth' => '01-01-1950',
                'gender' => 'MAN',
                'email' => 'henk@henkjong.nl',
                'mobileNumber' => null,
                'telephoneNumber' => '636178656',
                'address' => [
                    'streetName' => 'Pietersweg',
                    'houseNumber' => '4',
                    'houseNumberSuffix' => '1 A',
                    'postcode' => '8501GZ',
                    'city' => 'Apeldoorn',
                ],
                'huisartsNaam' => null,
                'huisartsPlaats' => null,
            ],
            'triage' => [
                'dateOfFirstSymptom' => '01-05-2021',
                'symptomsNote' => null,
                'temperature' => null,
                'healthNote' => null,
            ],
            'workLocation' => [
                'organisation' => 'Company X',
                'nameOfTheLocation' => null,
                'department' => null,
                'position' => null,
                'address' => [
                    'streetName' => null,
                    'houseNumber' => null,
                    'houseNumberSuffix' => null,
                    'postcode' => null,
                    'city' => null,
                ],
                'otherKnownCases' => [
                    null,
                ],
                'employmentType' => [
                    null,
                ],
            ],
            'test' => [
                'sampleDate' => '2021-01-19T08:52:01+01:00',
                'resultDate' => '2021-02-01T16:30:50.52Z',
                'sampleLocation' => 'Apeldoorn',
                'sampleId' => '000A0000000',
                'typeOfTest' => null,
                'result' => 'POSITIEF',
                'source' => 'CoronIT',
                'testLocation' => 'GGD Apeldoorn',
                'testLocationCategory' => 'GGD instance',
            ],
            'receivedAt' => CarbonImmutable::now()->toDateTimeString('microsecond'),
        ];
    }
}
