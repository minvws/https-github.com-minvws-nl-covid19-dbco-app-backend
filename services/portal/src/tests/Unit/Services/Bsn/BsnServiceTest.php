<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bsn;

use App\Exceptions\IdentifiedBsnNotValidAnymoreException;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use App\Services\Bsn\BsnService;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Testing\Assert;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use Tests\Unit\UnitTestCase;

#[Group('bsn')]
class BsnServiceTest extends UnitTestCase
{
    public function testConvertBsnAndDateOfBirthToPseudoBsn(): void
    {
        $bsn = $this->faker->uuid();
        $dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);
        $guid = $this->faker->uuid();
        $censoredBsn = $this->faker->numerify('******###');
        $letters = $this->faker->word;
        $organisationExternalId = $this->faker->uuid();

        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];

        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('convertBsnAndDateOfBirthToPseudoBsn')
            ->with($bsn, $dateOfBirth, $organisationExternalId)
            ->andReturn($pseudoBsnCollection);

        $bsnService = new BsnService($bsnRepository);
        $pseudoBsn = $bsnService->convertBsnAndDateOfBirthToPseudoBsn($bsn, $dateOfBirth, $organisationExternalId);

        $this->assertEquals($guid, $pseudoBsn->getGuid());
        $this->assertEquals($censoredBsn, $pseudoBsn->getCensoredBsn());
        $this->assertEquals($letters, $pseudoBsn->getLetters());
    }

    public function testConvertBsnAndDateOfBirthToPseudoBsnNoResults(): void
    {
        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('convertBsnAndDateOfBirthToPseudoBsn')->andReturn([]);

        $bsnService = new BsnService($bsnRepository);

        $this->expectException(BsnException::class);
        $bsnService->convertBsnAndDateOfBirthToPseudoBsn(
            $this->faker->uuid(),
            CarbonImmutable::instance($this->faker->dateTime),
            $this->faker->uuid(),
        );
    }

    public function testConvertBsnAndDateOfBirthToPseudoBsnTwoResults(): void
    {
        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('convertBsnAndDateOfBirthToPseudoBsn')
            ->andReturn([
                new PseudoBsn('', '', ''),
                new PseudoBsn('', '', ''),
            ]);

        $bsnService = new BsnService($bsnRepository);

        $this->expectException(BsnException::class);
        $bsnService->convertBsnAndDateOfBirthToPseudoBsn(
            $this->faker->uuid(),
            CarbonImmutable::instance($this->faker->dateTime),
            $this->faker->uuid(),
        );
    }

    public function testGetByPseudoBsnGuid(): void
    {
        $guid = $this->faker->uuid();
        $censoredBsn = $this->faker->numerify('******###');
        $letters = $this->faker->word();
        $organisationExternalId = $this->faker->uuid();

        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];

        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('getByPseudoBsnGuid')
            ->with($guid, $organisationExternalId)
            ->andReturn($pseudoBsnCollection);

        $bsnService = new BsnService($bsnRepository);
        $pseudoBsn = $bsnService->getByPseudoBsnGuid($guid, $organisationExternalId);

        $this->assertEquals($guid, $pseudoBsn->getGuid());
        $this->assertEquals($censoredBsn, $pseudoBsn->getCensoredBsn());
        $this->assertEquals($letters, $pseudoBsn->getLetters());
    }

    public function testGetByPseudoBsnGuidNoResults(): void
    {
        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('getByPseudoBsnGuid')->andReturn([]);

        $bsnService = new BsnService($bsnRepository);

        $this->expectException(BsnException::class);
        $bsnService->getByPseudoBsnGuid($this->faker->uuid(), $this->faker->uuid);
    }

    public function testGetByPseudoBsnGuidTwoResults(): void
    {
        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('getByPseudoBsnGuid')
            ->andReturn([
                new PseudoBsn('', '', ''),
                new PseudoBsn('', '', ''),
            ]);

        $bsnService = new BsnService($bsnRepository);

        $this->expectException(BsnException::class);
        $bsnService->getByPseudoBsnGuid($this->faker->uuid(), $this->faker->uuid);
    }

    public function testFindPseudoBsn(): void
    {
        $guid = $this->faker->uuid();
        $censoredBsn = $this->faker->numerify('******###');
        $letters = $this->faker->word();

        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];

        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('lookupPseudoBsn')->andReturn($pseudoBsnCollection);
        $bsnService = new BsnService($bsnRepository);

        $class = new ReflectionClass($bsnService);
        $method = $class->getMethod('findPseudoBsn');
        $method->setAccessible(true);

        $pseudoBsn = $method->invokeArgs($bsnService, [
            CarbonImmutable::now(),
            $this->faker->postcode,
            $this->faker->buildingNumber,
            'r2',
            $this->faker->uuid(),
        ]);

        $this->assertEquals($guid, $pseudoBsn[0]->getGuid());
        $this->assertEquals($censoredBsn, $pseudoBsn[0]->getCensoredBsn());
        $this->assertEquals($letters, $pseudoBsn[0]->getLetters());
    }

    public static function stripHouseNumberSuffixDataProvider(): Generator
    {
        yield '1 character' => [
            'house_number_suffix' => '2',
            'expects' => '2',
        ];

        yield '2 characters' => [
            'house_number_suffix' => 'r2',
            'expects' => 'r',
        ];

        yield '3 characters' => [
            'house_number_suffix' => '23r',
            'expects' => '2',
        ];

        yield 'invalid character' => [
            'house_number_suffix' => '-',
            'expects' => null,
        ];
    }

    #[DataProvider('stripHouseNumberSuffixDataProvider')]
    public function testStripHouseNumberSuffix(
        string $houseNumberSuffix,
        ?string $expects,
    ): void {
        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnService = new BsnService($bsnRepository);

        $class = new ReflectionClass($bsnService);
        $method = $class->getMethod('stripHouseNumberSuffix');
        $method->setAccessible(true);
        $response = $method->invokeArgs($bsnService, [$houseNumberSuffix]);

        Assert::assertEquals($response, $expects);
    }

    public function testNewExchangeTokenForIdentifiedBsn(): void
    {
        $pseudoBsnLookup = new PseudoBsnLookup(
            $this->faker->dateTime(),
            $this->faker->postcode,
            $this->faker->buildingNumber,
            $this->faker->optional()->randomLetter(),
        );
        $pseudoBsnGuid = $this->faker->uuid();
        $organisationExternalId = $this->faker->uuid();
        $newExchangeToken = $this->faker->uuid();

        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('lookupPseudoBsn')
            ->with($pseudoBsnLookup, $organisationExternalId)
            ->andReturn([new PseudoBsn($pseudoBsnGuid, $this->faker->uuid(), $this->faker->word())]);
        $bsnRepository->expects('getExchangeToken')
            ->with($pseudoBsnGuid, $organisationExternalId)
            ->andReturn($newExchangeToken);

        $bsnService = new BsnService($bsnRepository);
        $token = $bsnService->newExchangeTokenForIdentifiedPseudoBsn($pseudoBsnLookup, $pseudoBsnGuid, $organisationExternalId);

        $this->assertEquals($token, $newExchangeToken);
    }

    public function testNewExchangeTokenForIdentifiedBsnExpectsException(): void
    {
        $pseudoBsnLookup = new PseudoBsnLookup(
            $this->faker->dateTime(),
            $this->faker->postcode,
            $this->faker->buildingNumber,
            $this->faker->optional()->randomLetter(),
        );

        $bsnRepository = Mockery::mock(BsnRepository::class);
        $bsnRepository->expects('lookupPseudoBsn')
            ->andReturn([new PseudoBsn($this->faker->uuid(), $this->faker->uuid(), $this->faker->word())]);

        $bsnService = new BsnService($bsnRepository);

        $this->expectException(IdentifiedBsnNotValidAnymoreException::class);
        $bsnService->newExchangeTokenForIdentifiedPseudoBsn($pseudoBsnLookup, $this->faker->uuid(), $this->faker->uuid);
    }
}
