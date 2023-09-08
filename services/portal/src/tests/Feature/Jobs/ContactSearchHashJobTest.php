<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ContactSearchHashJob;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
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
final class ContactSearchHashJobTest extends FeatureTestCase
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

        $this->app->call([new ContactSearchHashJob($case->uuid), 'handle']);

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
        Bus::fake()->except(ContactSearchHashJob::class);

        $case = $this->saveCase($caseCreateClosure);

        Bus::fake();

        $caseUpdateClosure($case);
        $case->save();

        $this->app->call([new ContactSearchHashJob($case->uuid), 'handle']);

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
                    'dateOfBirth#phone' => '06 12345678#19500101',
                ],
            ],
            'missing contact->phone data' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                    });
                },
                'expectedHashKeys' => [],
            ],
            'missing index->dateOfBirth data' => [
                'caseClosure' => static function (EloquentCase $case): void {
                    $case->contact = Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                        $contact->phone = self::getCaseFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [],
            ],
        ];
    }

    public static function getHandleForUpdatesData(): array
    {
        return [
            'update contact->phone' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->contact->phone = '06 12345999';
                },
                'expectedHashes' => [
                    'dateOfBirth#phone' => '06 12345999#19500101',
                ],
            ],
            'update index->dateOfBirth' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->dateOfBirth = CarbonImmutable::createFromFormat('Y-m-d', '1960-05-05');
                },
                'expectedHashes' => [
                    'dateOfBirth#phone' => '06 12345678#19600505',
                ],
            ],
            'remove index->dateOfBirth' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->index->dateOfBirth = null;
                },
                'expectedHashes' => [],
            ],
            'remove contact->phone' => [
                'caseCreateClosure' => self::getCaseFixtureAsClosure(),
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->contact->phone = null;
                },
                'expectedHashes' => [],
            ],
            'update contact->phone with contact->phone not being set before' => [
                'caseCreateClosure' => static function (EloquentCase $case): void {
                    $case->index = Index::newInstanceWithVersion(1, static function (Index $index): void {
                        $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
                    });
                },
                'caseUpdateClosure' => static function (EloquentCase $case): void {
                    $case->contact->phone = self::getCaseFixtureData('phone');
                },
                'expectedHashKeys' => [
                    'dateOfBirth#phone' => '06 12345678#19500101',
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
                $index->dateOfBirth = self::getCaseFixtureData('dateOfBirth');
            });
            $case->contact = Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                $contact->phone = self::getCaseFixtureData('phone');
            });
        };
    }

    private static function getCaseFixtureData(string $key): string|DateTimeInterface
    {
        $data = [
            'dateOfBirth' => CarbonImmutable::createFromFormat('Y-m-d', '1950-01-01'),
            'phone' => '+31612345678',
        ];

        assert(isset($data[$key]), new RuntimeException(sprintf('No case fixture data for key "%s"', $key)));

        return $data[$key];
    }
}
