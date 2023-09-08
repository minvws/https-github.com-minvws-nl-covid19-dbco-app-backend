<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Bsn;

use App\Http\Client\Guzzle\MittensClientException;
use App\Http\Client\Guzzle\MittensClientInterface;
use App\Http\Requests\Mittens\MittensRequest;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use App\Repositories\Bsn\Mittens\MittensBsnRepository;
use Carbon\CarbonImmutable;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Client\RequestExceptionInterface;
use Tests\Unit\UnitTestCase;

use function json_encode;

#[Group('bsn')]
#[Group('guzzle')]
class MittensBsnRepositoryTest extends UnitTestCase
{
    private const PSEUDO_BSN_FIXTURE = '9fc3e93e-e24d-4064-5717-7b4b41cb8993';
    private const CENCORED_BSN_FIXTURE = '******123';
    private const LETTERS_FIXTURE = 'EJ';
    private const PSEUDO_BSN_TOKENS_FOR_FIXTURE = ['secure_mail'];
    private const ACCESS_CODE_IDENTIFIER = '01003';
    private const PII_ACCESS_CODE = 'pii_01003';
    private const DIGID_ACCESS_CODE = 'digid_01003';

    public function testConvertBsnToPseudoBsnSingleResult(): void
    {
        $bsn = $this->faker->idNumber;

        $responseGuid = self::PSEUDO_BSN_FIXTURE;
        $responseCensoredBsn = self::CENCORED_BSN_FIXTURE;
        $responseLetters = self::LETTERS_FIXTURE;
        $pseudoBsnToken = $this->faker->uuid();

        $response = (object) [
            'data' => [
                (object) [
                    'guid' => $responseGuid,
                    'censored_bsn' => $responseCensoredBsn,
                    'letters' => $responseLetters,
                    'token' => $pseudoBsnToken,
                ],
            ],
        ];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->with(
                $this->callback(static function (MittensRequest $request) use ($bsn) {
                    return $request->url === '/service/via_digid/' &&
                        $request->getJsonBody() === json_encode([
                            'digid_access_token' => self::DIGID_ACCESS_CODE,
                            'BSN' => $bsn,
                        ]);
                }),
            )
            ->willReturn($response);

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $lookupResponse = $mittensBsnRepository->convertBsnToPseudoBsn($bsn, self::ACCESS_CODE_IDENTIFIER);

        $expected = [new PseudoBsn($responseGuid, $responseCensoredBsn, $responseLetters)];
        $this->assertEquals($expected, $lookupResponse);
    }

    public function testConvertBsnToPseudoBsnMultipleResults(): void
    {
        $bsn = $this->faker->idNumber;

        $response = (object) [
            'data' => [
                $this->generateMittensResultSet(),
                $this->generateMittensResultSet(),
            ],
        ];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->with(
                $this->callback(static function (MittensRequest $request) use ($bsn) {
                    return $request->url === '/service/via_digid/' &&
                        $request->getJsonBody() === json_encode([
                            'digid_access_token' => self::DIGID_ACCESS_CODE,
                            'BSN' => $bsn,
                        ]);
                }),
            )
            ->willReturn($response);

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $lookupResponse = $mittensBsnRepository->convertBsnToPseudoBsn($bsn, self::ACCESS_CODE_IDENTIFIER);

        $this->assertCount(2, $lookupResponse);
    }

    public function testGetByPseudoBsnGuid(): void
    {
        $pseudoBsnGuid = $this->faker->uuid();

        $responseGuid = self::PSEUDO_BSN_FIXTURE;
        $responseCensoredBsn = self::CENCORED_BSN_FIXTURE;
        $responseLetters = self::LETTERS_FIXTURE;

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())
            ->method('post')
            ->with($this->callback(static function (MittensRequest $request) use ($pseudoBsnGuid) {
                return $request->url === '/service/via_uuid/' &&
                    $request->getJsonBody() === json_encode([
                        'access_token' => self::PII_ACCESS_CODE,
                        'UUID' => $pseudoBsnGuid,
                    ]);
            }))
            ->willReturn((object) [
                'data' => [
                    (object) [
                        'guid' => self::PSEUDO_BSN_FIXTURE,
                        'censored_bsn' => self::CENCORED_BSN_FIXTURE,
                        'letters' => self::LETTERS_FIXTURE,
                    ],
                ]]);

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $actual = $mittensBsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, self::ACCESS_CODE_IDENTIFIER);

        $expected = [new PseudoBsn($responseGuid, $responseCensoredBsn, $responseLetters)];
        $this->assertEquals($expected, $actual);
    }

    public function testConvertBsnAndDateOfBirthToPeudoBsn(): void
    {
        $bsn = $this->faker->idNumber;

        $responseGuid = self::PSEUDO_BSN_FIXTURE;
        $responseCensoredBsn = self::CENCORED_BSN_FIXTURE;
        $responseLetters = self::LETTERS_FIXTURE;
        $dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);

        $response = (object) [
            'data' => [
                (object) [
                    'guid' => $responseGuid,
                    'censored_bsn' => $responseCensoredBsn,
                    'letters' => $responseLetters,
                ],
            ],
        ];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->with(
                $this->callback(static function (MittensRequest $request) use ($bsn, $dateOfBirth) {
                    return $request->url === '/service/via_digid/' &&
                        $request->getJsonBody() === json_encode([
                            'digid_access_token' => self::DIGID_ACCESS_CODE,
                            'BSN' => $bsn,
                            'birthdate' => $dateOfBirth->format('Ymd'),
                        ]);
                }),
            )
            ->willReturn($response);

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $lookupResponse = $mittensBsnRepository->convertBsnAndDateOfBirthToPseudoBsn($bsn, $dateOfBirth, self::ACCESS_CODE_IDENTIFIER);

        $expected = [new PseudoBsn($responseGuid, $responseCensoredBsn, $responseLetters)];
        $this->assertEquals($expected, $lookupResponse);
    }

    public function testLookupPseudoBsnSingleResult(): void
    {
        $requestDateOfBirth = '2000-01-01';
        $requestPostalCode = '1234   ab    ';
        $requestHouseNumber = '123';
        $requestHouseNumberSuffix = 'a';

        $responseGuid = self::PSEUDO_BSN_FIXTURE;
        $responseCensoredBsn = self::CENCORED_BSN_FIXTURE;
        $responseLetters = self::LETTERS_FIXTURE;

        $expectedRequestBody = [
            'access_token' => self::PII_ACCESS_CODE,
            'birthdate' => CarbonImmutable::createFromFormat('Y-m-d', $requestDateOfBirth)->format('Ymd'),
            'zipcode' => '1234AB',
            'house_number' => $requestHouseNumber,
            'tokens_for' => self::PSEUDO_BSN_TOKENS_FOR_FIXTURE,
            'house_letter' => $requestHouseNumberSuffix,
        ];

        $response = (object) [
            'data' => [
                (object) [
                    'guid' => $responseGuid,
                    'censored_bsn' => $responseCensoredBsn,
                    'letters' => $responseLetters,
                ],
            ],
        ];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->with($this->callback(static function (MittensRequest $request) use ($expectedRequestBody) {
                return $request->url === '/service/via_pii/' &&
                    $request->getJsonBody() === json_encode($expectedRequestBody);
            }))
            ->willReturn($response);

        $pseudoBsnLookup = new PseudoBsnLookup(
            CarbonImmutable::createFromFormat('Y-m-d', $requestDateOfBirth),
            $requestPostalCode,
            $requestHouseNumber,
            $requestHouseNumberSuffix,
        );

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $actual = $mittensBsnRepository->lookupPseudoBsn($pseudoBsnLookup, self::ACCESS_CODE_IDENTIFIER);

        $expected = [new PseudoBsn($responseGuid, $responseCensoredBsn, $responseLetters)];
        $this->assertEquals($expected, $actual);
    }

    public function testLookupPseudoBsnMultipleResults(): void
    {
        $response = (object) [
            'data' => [
                $this->generateMittensResultSet(),
                $this->generateMittensResultSet(),
            ],
        ];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->with($this->callback(static function (MittensRequest $request) {
                return $request->toGuzzleRequest()->getUri()->getPath() === '/service/via_pii/';
            }))
            ->willReturn($response);

        $pseudoBsnLookup = new PseudoBsnLookup(
            CarbonImmutable::createFromFormat('Y-m-d', '2001-01-01'),
            '1234AB',
            '123',
            'a',
        );
        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);
        $actual = $mittensBsnRepository->lookupPseudoBsn($pseudoBsnLookup, self::ACCESS_CODE_IDENTIFIER);

        $this->assertCount(2, $actual);
    }

    public function testMittensBsnRepositoryNoResults(): void
    {
        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->willThrowException(
                new MittensClientException(
                    'No answer found for this query.',
                    400,
                    $this->createMock(ClientException::class),
                ),
            );

        $pseudoBsnLookup = new PseudoBsnLookup(
            CarbonImmutable::createFromFormat('Y-m-d', '2001-01-01'),
            '1234AB',
            '123',
            'a',
        );
        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('No answer found for this query.');
        $mittensBsnRepository->lookupPseudoBsn($pseudoBsnLookup, self::ACCESS_CODE_IDENTIFIER);
    }

    public function testMittensBsnRepositoryConnectionError(): void
    {
        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')
            ->willThrowException(
                new MittensClientException(
                    'Error Communicating with Server',
                    0,
                    $this->createMock(RequestExceptionInterface::class),
                ),
            );

        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('Error Communicating with Server');
        $this->expectExceptionCode(0);

        $mittensBsnRepository->lookupPseudoBsn(
            new PseudoBsnLookup(
                CarbonImmutable::createFromFormat('Y-m-d', '2001-01-01'),
                '1234AB',
                '123',
                'a',
            ),
            self::ACCESS_CODE_IDENTIFIER,
        );
    }

    public function testMittensBsnRepositoryUnknownAccessToken(): void
    {
        $pseudoBsnLookup = new PseudoBsnLookup(
            CarbonImmutable::createFromFormat('Y-m-d', '2001-01-01'),
            '1234AB',
            '123',
            'a',
        );

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('unknown access token identifier given');
        $mittensBsnRepository->lookupPseudoBsn($pseudoBsnLookup, 'foo');
    }

    public function testMittensBsnRepositoryNoDataInResponse(): void
    {
        $response = (object) ['no-data-in-response' => []];

        $mittensClient = $this->createMock(MittensClientInterface::class);
        $mittensClient->expects($this->once())->method('post')->willReturn($response);

        $pseudoBsnLookup = new PseudoBsnLookup(
            CarbonImmutable::createFromFormat('Y-m-d', '2001-01-01'),
            '1234AB',
            '123',
            'a',
        );
        $mittensBsnRepository = $this->getMittensBsnRepository($mittensClient);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('no data-field in response');
        $mittensBsnRepository->lookupPseudoBsn($pseudoBsnLookup, self::ACCESS_CODE_IDENTIFIER);
    }

    private function getMittensBsnRepository(MittensClientInterface $client): MittensBsnRepository
    {
        return new MittensBsnRepository(
            $client,
            [self::ACCESS_CODE_IDENTIFIER => self::DIGID_ACCESS_CODE],
            [self::ACCESS_CODE_IDENTIFIER => self::PII_ACCESS_CODE],
            self::PSEUDO_BSN_TOKENS_FOR_FIXTURE,
        );
    }

    private function generateMittensResultSet(): object
    {
        return (object) [
            'guid' => $this->faker->uuid(),
            'censored_bsn' => $this->faker->idNumber,
            'letters' => $this->faker->randomElement([
                $this->faker->lexify('?'),
                $this->faker->lexify('??'),
                $this->faker->lexify('???'),
            ]),
        ];
    }
}
