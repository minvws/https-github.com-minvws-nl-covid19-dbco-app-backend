<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\IndexSearchHashJob;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\CovidCaseSearch;
use App\Models\Eloquent\EloquentCase;
use App\Services\SearchHash\Hasher\Hasher;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use App\Services\SearchHash\Normalizer\Normalizer;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function assert;
use function count;
use function sprintf;

#[Group('search-hash')]
final class IndexSearchHashJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Hasher::class, NoOpHasher::class);
        $this->app->bind(Normalizer::class, NoOpNormalizer::class);
    }

    /**
     * @param Closure(EloquentCase):void $caseClosure
     */
    #[DataProvider('getHandleForInitialCreationData')]
    public function testHandleForInitialCreation(Closure $caseClosure, array $expectedHashKeys): void
    {
        Bus::fake();

        $case = $this->saveCase($caseClosure);

        $this->app->call([new IndexSearchHashJob($case->uuid), 'handle']);

        $hashes = CovidCaseSearch::where('covidcase_uuid', $case->uuid)->pluck('hash', 'key');

        $this->assertCount(count($expectedHashKeys), $hashes);

        foreach ($expectedHashKeys as $key => $hash) {
            $this->assertArrayHasKey($key, $hashes, sprintf('Result did not contain expected hash key "%s"', $key));
            $this->assertSame($hashes[$key], $hash);
        }
    }

    /**
     * @param Closure(EloquentCase):void $caseCreateClosure
     * @param Closure(EloquentCase):void $caseUpdateClosure
     */
    #[DataProvider('getHandleForUpdatesData')]
    public function testHandleForUpdates(
        Closure $caseCreateClosure,
        Closure $caseUpdateClosure,
        array $expectedHashKeys,
    ): void {
        Bus::fake()->except(IndexSearchHashJob::class);

        $case = $this->saveCase($caseCreateClosure);

        Bus::fake();

        $caseUpdateClosure($case);
        $case->save();

        $this->app->call([new IndexSearchHashJob($case->uuid), 'handle']);

        $hashes = CovidCaseSearch::where('covidcase_uuid', $case->uuid)->pluck('hash', 'key');

        $this->assertCount(count($expectedHashKeys), $hashes);

        foreach ($expectedHashKeys as $key => $hash) {
            $this->assertArrayHasKey($key, $hashes);
            $this->assertSame($hash, $hashes[$key]);
        }
    }

    public static function getHandleForInitialCreationData(): array
    {
        return [
            'all data available to create all hashes' => [
                'caseClosure' => self::getCaseFixtureAsClosure(),
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                ],
            ],
            'missing index->lastname' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                            $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                        $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                            $address->postalCode = self::getCaseFixtureData('postalCode');
                            $address->houseNumber = self::getCaseFixtureData('houseNumber');
                            $address->houseNumberSuffix = self::getCaseFixtureData('houseNumberSuffix');
                        });
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                ],
            ],
            'missing index->dateOfBirth' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->lastname = self::getCaseFixtureData('lastname');
                        $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                        $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                            $address->postalCode = self::getCaseFixtureData('postalCode');
                            $address->houseNumber = self::getCaseFixtureData('houseNumber');
                            $address->houseNumberSuffix = self::getCaseFixtureData('houseNumberSuffix');
                        });
                    });
                },
                'expectedHashKeys' => [],
            ],
            'missing index->address->postal' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->lastname = self::getCaseFixtureData('lastname');
                        $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                        $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                            $address->houseNumber = self::getCaseFixtureData('houseNumber');
                            $address->houseNumberSuffix = self::getCaseFixtureData('houseNumberSuffix');
                        });
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                ],
            ],
            'missing index->address->houseNumberSuffix' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->lastname = self::getCaseFixtureData('lastname');
                        $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                        $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                            $address->postalCode = self::getCaseFixtureData('postalCode');
                            $address->houseNumber = self::getCaseFixtureData('houseNumber');
                        });
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX',
                ],
            ],
        ];
    }

    public static function getHandleForUpdatesData(): array
    {
        return [
            'update index->lastname' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->lastname = 'de Huis';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#de Huis',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                ],
            ],
            'update index->address->houseNumber' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->address->houseNumber = '99';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '99#19500101#9999XX#a',
                ],
            ],
            'update index->lastThreeBsnDigits and index->address->postal' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->bsnCensored = '******919';
                    $case->index->address->postalCode = '1111Ba';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '919#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#1111Ba#19500101#a',
                ],
            ],
            'update lastname with lastname not being set before' => [
                'caseCreateClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                        $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                            $address->postalCode = self::getCaseFixtureData('postalCode');
                            $address->houseNumber = self::getCaseFixtureData('houseNumber');
                            $address->houseNumberSuffix = self::getCaseFixtureData('houseNumberSuffix');
                        });
                    });
                },
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->lastname = 'de Huis';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#de Huis',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                ],
            ],
            'remove index->address->postalCode' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->address->postalCode = null;
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                ],
            ],
            'remove index->lastname' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->lastname = null;
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                ],
            ],
        ];
    }

   /**
    * @param Closure(EloquentCase):void $caseClosure
    */
    private function saveCase(Closure $caseClosure): EloquentCase
    {
        $case = EloquentCase::newInstanceWithVersion(1, static function (EloquentCase $case): void {
            $case->setRawAttributes(EloquentCase::factory()->make()->getAttributes());
        });

        $caseClosure($case);

        $case->saveOrFail();

        return $case;
    }

    private static function getCaseFixtureAsClosure(): closure
    {
        return static function (EloquentCase $case): void {
            $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                $index->lastname = self::getCaseFixtureData('lastname');
                $index->bsnCensored = self::getCaseFixtureData('bsnCensored');
                $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                    $address->postalCode = self::getCaseFixtureData('postalCode');
                    $address->houseNumber = self::getCaseFixtureData('houseNumber');
                    $address->houseNumberSuffix = self::getCaseFixtureData('houseNumberSuffix');
                });
            });
        };
    }

    private static function getCaseFixtureData(string $key): string|DateTimeInterface
    {
        $data = [
            'lastname' => 'Doe',
            'bsnCensored' => '******286',
            'dateOfBirth' => CarbonImmutable::createFromFormat('Y-m-d', '1950-01-01'),
            'postalCode' => '9999XX',
            'houseNumber' => '01',
            'houseNumberSuffix' => 'a',
        ];

        assert(isset($data[$key]), new RuntimeException(sprintf('No case fixture data for key "%s"', $key)));

        return $data[$key];
    }
}
