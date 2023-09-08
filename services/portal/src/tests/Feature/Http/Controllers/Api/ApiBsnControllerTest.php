<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\OrganisationType;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;
use function array_merge;
use function sprintf;
use function strtoupper;

#[Group('bsn')]
class ApiBsnControllerTest extends FeatureTestCase
{
    #[DataProvider('bsnLookupAuthorizationProvider')]
    public function testLookupAuthorization(string $roles, int $expectedStatus): void
    {
        $organisation = $this->createOrganisation(['external_id' => '24680']);
        $user = $this->createUserForOrganisation($organisation, [], $roles);

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ]);

        $response->assertStatus($expectedStatus);
    }

    /**
     * @return array
     */
    public static function bsnLookupAuthorizationProvider(): array
    {
        return [
            'user' => ['user', 200],
            'user_nationwide' => ['user', 200],
            'planner' => ['planner', 200],
            'compliance' => ['compliance', 403],
        ];
    }

    #[DataProvider('validLookupDataProvider')]
    public function testLookupSingleResult(array $postData): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $guid = $this->faker->uuid();
        $censoredBsn = '******123';
        $letters = strtoupper(sprintf('%s%s', $this->faker->randomLetter, $this->faker->randomLetter));
        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use ($organisation, $postData, $pseudoBsnCollection): void {
                $mock->expects('lookupPseudoBsn')
                    ->withArgs(static function (
                        PseudoBsnLookup $request,
                        string $organisationExternalId,
                    ) use (
                        $organisation,
                        $postData,
                    ) {
                        $dateofBirth = CarbonImmutable::createFromFormat('Y-m-d', $postData['dateOfBirth'])->floorDay();
                        if (!$dateofBirth->equalTo($request->dateOfBirth)) {
                            return false;
                        }

                        if ($request->postalCode !== $postData['postalCode']) {
                            return false;
                        }

                        if ($request->houseNumber !== $postData['houseNumber']) {
                            return false;
                        }

                        if (
                            array_key_exists('houseNumberSuffix', $postData) &&
                            $request->houseNumberSuffix !== $postData['houseNumberSuffix']
                        ) {
                            return false;
                        }

                        return $organisationExternalId === $organisation->external_id;
                    })
                    ->andReturn($pseudoBsnCollection);
                $mock->expects('convertBsnToPseudoBsn')
                    ->between(0, 1)
                    ->with('123123123', $organisation->external_id)
                    ->andReturn($pseudoBsnCollection);
            },
        );

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', $postData);

        $response->assertStatus(200);

        $expectedResponseBody = [
            'guid' => $guid,
            'censoredBsn' => $censoredBsn,
            'letters' => $letters,
        ];
        $this->assertEquals($expectedResponseBody, $response->json());
    }

    public static function validLookupDataProvider(): array
    {
        $postData = [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ];

        return [
            'minimal' => [$postData],
            'with houseNumberSuffix' => [array_merge($postData, ['houseNumberSuffix' => 'a'])],
            'valid postalcode format' => [array_merge($postData, ['postalCode' => '1234 ab'])],
            'with bsn' => [
                [
                    'dateOfBirth' => '2001-01-01',
                    'postalCode' => '1234AB',
                    'houseNumber' => '1',
                    'bsn' => '123123123',
                ]],
        ];
    }

    public function testLookupSingleResultUsesParentOrganisationExternalId(): void
    {
        $regionalOrganisation = $this->createOrganisation([
            'external_id' => 'regionalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);
        $outsourceOrganisation = $this->createOrganisation([
            'external_id' => 'outsourceOrganisationId',
            'type' => OrganisationType::outsourceDepartment(),
        ]);
        $regionalOrganisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $user = $this->createUserForOrganisation($outsourceOrganisation, [], 'user_nationwide');

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use ($regionalOrganisation): void {
                $mock->expects('lookupPseudoBsn')
                    ->withArgs(static function (
                        PseudoBsnLookup $request,
                        string $organisationExternalId,
                    ) use ($regionalOrganisation) {
                        return $organisationExternalId === $regionalOrganisation->external_id;
                    });
            },
        );

        $this->be($user)->postJson('/api/pseudo-bsn/lookup', [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ]);
    }

    public function testLookupSingleResultUsesWithoutParentOrganisation(): void
    {
        $outsourceOrganisation = $this->createOrganisation([
            'external_id' => 'outsourceOrganisationId',
            'type' => OrganisationType::outsourceDepartment(),
        ]);

        $user = $this->createUserForOrganisation($outsourceOrganisation, [], 'user_nationwide');

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['error' => 'No parent (regional) organisation found']);
    }

    #[DataProvider('invalidLookupDataProvider')]
    public function testLookupValidation(array $postData): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', $postData);
        $response->assertStatus(422);
    }

    public static function invalidLookupDataProvider(): array
    {
        $postData = [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ];

        return [
            'invalid dateOfBirth format' => [array_merge($postData, ['dateOfBirth' => '01-01-2000'])],
            'invalid dateOfBirth' => [array_merge($postData, ['dateOfBirth' => 'foo'])],
            'missing dateOfBirth' => [['houseNumber' => '1', 'postalCode' => '1234AB']],
            'missing postalCode' => [['dateOfBirth' => '2001-01-01', 'houseNumber' => '1']],
            'missing houseNumber' => [['dateOfBirth' => '2001-01-01', 'postalCode' => '1234AB']],
            'invalid postalCode' => [array_merge($postData, ['postalCode' => 'foo'])],
            'invalid bsn' => [
                [
                    'dateOfBirth' => '2001-01-01',
                    'postalCode' => '1234AB',
                    'houseNumber' => '1',
                    'bsn' => '123456',
                ]],
            'bsn prohibits lastThreeDigits' => [array_merge($postData, ['bsn' => '123456789'])],
        ];
    }

    public function testLookupCorrectLastThreeDigits(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $postData = [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ];

        $pseudoBsnCollection = [];
        foreach (['******123'] as $index => $bsn) {
            $guid = sprintf('9fc3e93e-e24d-4064-5717-7b4b41cb899%s', $index);
            $pseudoBsnCollection[] = new PseudoBsn($guid, $bsn, 'EJ');
        }

        $this->mock(BsnRepository::class, static function (MockInterface $mock) use ($pseudoBsnCollection): void {
            $mock->expects('lookupPseudoBsn')->andReturn($pseudoBsnCollection);
        });

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', $postData);
        $response->assertStatus(Response::HTTP_OK);

        $expectedResponseBody = [
            'guid' => '9fc3e93e-e24d-4064-5717-7b4b41cb8990',
            'censoredBsn' => '******123',
            'letters' => 'EJ',
        ];
        $this->assertEquals($expectedResponseBody, $response->json());
    }

    #[DataProvider('incorrectLastThreeDigitsDataProvider')]
    public function testLookupIncorrectLastThreeDigits(array $postData, array $bsns, string $error): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $pseudoBsnCollection = [];
        foreach ($bsns as $index => $bsn) {
            $guid = sprintf('9fc3e93e-e24d-4064-5717-7b4b41cb899%s', $index);
            $pseudoBsnCollection[] = new PseudoBsn($guid, $bsn, 'EJ');
        }

        $this->mock(BsnRepository::class, static function (MockInterface $mock) use ($pseudoBsnCollection): void {
            $mock->expects('lookupPseudoBsn')->andReturn($pseudoBsnCollection);
        });

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', $postData);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(['error' => $error], $response->json());
    }

    public static function incorrectLastThreeDigitsDataProvider(): array
    {
        $postData = [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ];

        return [
            'No results' => [
                'postData' => $postData,
                'bsns' => [],
                'error' => 'No matching results found',
            ],
            'One result, not correct' => [
                'postData' => $postData,
                'bsns' => [
                    '******321',
                ],
                'error' => 'No matching results found',
            ],
            'Two results, both incorrect' => [
                'postData' => $postData,
                'bsns' => [
                    '******321',
                    '******987',
                ],
                'error' => 'Too many matching results found',
            ],
            'Two results, one correct' => [
                'postData' => $postData,
                'bsns' => [
                    '******123',
                    '******987',
                ],
                'error' => 'Too many matching results found',
            ],
            'Two results, both correct' => [
                'postData' => $postData,
                'bsns' => [
                    '******123',
                    '******123',
                ],
                'error' => 'Too many matching results found',
            ],
        ];
    }

    public function testErrorWhenServiceNotAvailable(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $postData = [
            'dateOfBirth' => '2001-01-01',
            'lastThreeDigits' => '123',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
        ];

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('lookupPseudoBsn')
                ->andThrow(BsnException::class, 'some error');
        });

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', $postData);
        $this->assertEquals(['error' => 'some error'], $response->json());
    }

    #[DataProvider('lookupFullBsnDataProvider')]
    public function testLookupWithFullBsn(string $convertResultUuid): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $pseudoBsnLookupResult = [
            new PseudoBsn('uuid1', '******123', 'AB'),
            new PseudoBsn('uuid2', '******123', 'AB'),
        ];
        $pseudoBsnConvertResult = [
            new PseudoBsn($convertResultUuid, '******123', 'AB'),
        ];

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use (
                $organisation,
                $pseudoBsnLookupResult,
                $pseudoBsnConvertResult,
            ): void {
                $mock->expects('lookupPseudoBsn')->andReturn($pseudoBsnLookupResult);
                $mock->expects('convertBsnToPseudoBsn')
                    ->with('123123123', $organisation->external_id)
                    ->andReturn($pseudoBsnConvertResult);
            },
        );

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', [
            'dateOfBirth' => '2000-01-01',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
            'bsn' => '123123123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'guid' => $convertResultUuid,
            'censoredBsn' => '******123',
            'letters' => 'AB',
        ]);
    }

    public static function lookupFullBsnDataProvider(): array
    {
        return [
            'first result match' => ['uuid1'],
            'second result match' => ['uuid2'],
        ];
    }

    public function testLookupWithBsnMismatch(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation);

        $pseudoBsnLookupResult = [
            new PseudoBsn('uuid1', '******123', 'AB'),
            new PseudoBsn('uuid2', '******123', 'AB'),
        ];
        $pseudoBsnConvertResult = [
            new PseudoBsn('uuid3', '******123', 'AB'),
        ];

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use (
                $organisation,
                $pseudoBsnLookupResult,
                $pseudoBsnConvertResult,
            ): void {
                $mock->expects('lookupPseudoBsn')->andReturn($pseudoBsnLookupResult);
                $mock->expects('convertBsnToPseudoBsn')
                    ->with('123123123', $organisation->external_id)
                    ->andReturn($pseudoBsnConvertResult);
            },
        );

        $response = $this->be($user)->postJson('/api/pseudo-bsn/lookup', [
            'dateOfBirth' => '2000-01-01',
            'postalCode' => '1234AB',
            'houseNumber' => '1',
            'bsn' => '123123123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['error' => 'given bsn does not match any from lookup']);
    }
}
