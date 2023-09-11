<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\EloquentTaskSearchHashJob;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\TaskSearch;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Models\Task\TaskAddress;
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
final class EloquentTaskSearchHashJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Hasher::class, NoOpHasher::class);
        $this->app->bind(Normalizer::class, NoOpNormalizer::class);
    }

   /**
     * @param Closure(EloquentTask):void $taskClosure
     */
    #[DataProvider('getHandleForInitialCreationData')]
    public function testHandleForInitialCreation(Closure $taskClosure, array $expectedHashKeys): void
    {
        Bus::fake();

        $task = $this->saveTask($taskClosure);

        $this->app->call([new EloquentTaskSearchHashJob($task->uuid), 'handle']);

        $hashes = TaskSearch::where('task_uuid', $task->uuid)->pluck('hash', 'key');

        $this->assertCount(count($expectedHashKeys), $hashes);

        foreach ($expectedHashKeys as $key => $hash) {
            $this->assertArrayHasKey($key, $hashes, sprintf('Result did not contain expected hash key "%s"', $key));
            $this->assertSame($hashes[$key], $hash);
        }
    }

   /**
    * @param Closure(EloquentTask):void $taskCreateClosure
     * @param Closure(EloquentTask):void $taskUpdateClosure
     */
    #[DataProvider('getHandleForUpdatesData')]
    public function testHandleForUpdates(
        Closure $taskCreateClosure,
        Closure $taskUpdateClosure,
        array $expectedHashKeys,
    ): void {
        Bus::fake()->except(EloquentTaskSearchHashJob::class);

        $task = $this->saveTask($taskCreateClosure);

        Bus::fake();

        $taskUpdateClosure($task);
        $task->save();

        $this->app->call([new EloquentTaskSearchHashJob($task->uuid), 'handle']);

        $hashes = TaskSearch::where('task_uuid', $task->uuid)->pluck('hash', 'key');

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
                'taskClosure' => self::getTaskFixtureAsClosure(),
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'missing general->lastname' => [
                'taskClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                                $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'missing personalDetails->dateOfBirth' => [
                'caseClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [],
            ],
            'missing personalDetails->address->postal' => [
                'caseClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'missing personalDetails->address->houseNumberSuffix' => [
                'caseClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'missing personalDetails->address' => [
                'caseClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'missing all data' => [
                'caseClosure' => static function (EloquentTask $task): void {
                },
                'expectedHashKeys' => [],
            ],
            'missing general->phone' => [
                'taskClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                    });
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                ],
            ],
        ];
    }

    public static function getHandleForUpdatesData(): array
    {
        return [
            'update personalDetails->lastname' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->lastname = 'de Huis';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#de Huis',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'update personalDetails->address->houseNumber' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details->address->houseNumber = '99';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '99#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'update general->phone' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->phone = '06 12345678';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '06 12345678#19500101',
                ],
            ],
            'update general->lastThreeBsnDigits and personalDetails->address->postal' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details->bsnCensored = '******919';
                    $task->personal_details->address->postalCode = '1111Ba';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#1111Ba#19500101#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '919#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'update general->lastname with general->lastname not being set before' => [
                'taskCreateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                        $general->phone = self::getTaskFixtureData('phone');
                    });
                },
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->lastname = 'de Huis';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#de Huis',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'update general->phone with general->phone not being set before' => [
                'taskCreateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails): void {
                            $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                            $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address): void {
                                    $address->postalCode = self::getTaskFixtureData('postalCode');
                                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                                },
                            );
                        },
                    );
                    $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                        $general->lastname = self::getTaskFixtureData('lastname');
                    });
                },
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->phone = '06 12345678';
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '06 12345678#19500101',
                ],
            ],
            'update personalDetails->dateOfBirth' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details->dateOfBirth = CarbonImmutable::createFromFormat('Y-m-d', '1960-10-02');
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19601002#9999XX#a',
                    'dateOfBirth#lastname' => '19601002#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19601002',
                    'dateOfBirth#phone' => '071 188 0731#19601002',
                ],
            ],
            'remove personalDetails->address->postalCode' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details->address->postalCode = null;
                },
                'expectedHashKeys' => [
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'remove general->lastname' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->lastname = null;
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                    'dateOfBirth#phone' => '071 188 0731#19500101',
                ],
            ],
            'remove personalDetails->dateOfBirth' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->personal_details->dateOfBirth = null;
                },
                'expectedHashKeys' => [],
            ],
            'remove general->phone' => [
                'taskCreateClosure' => self::getTaskFixtureAsClosure(),
                'taskUpdateClosure' => static function (EloquentTask $task): void {
                    $task->general->phone = null;
                },
                'expectedHashKeys' => [
                    'dateOfBirth#houseNumber#houseNumberSuffix#postalCode' => '01#19500101#9999XX#a',
                    'dateOfBirth#lastname' => '19500101#Doe',
                    'dateOfBirth#lastThreeBsnDigits' => '286#19500101',
                ],
            ],
        ];
    }

   /**
    * @param Closure(EloquentTask):void $taskClosure
    */
    private function saveTask(Closure $taskClosure): EloquentTask
    {
        $task = EloquentTask::newInstanceWithVersion(1, static function (EloquentTask $task): void {
            $task->setRawAttributes(EloquentTask::factory()->make()->getAttributes());
        });

        $taskClosure($task);

        $task->saveOrFail();

        return $task;
    }

    private static function getTaskFixtureAsClosure(): closure
    {
        return static function (EloquentTask $task): void {
            $task->personal_details = PersonalDetails::newInstanceWithVersion(1, static function (PersonalDetails $personalDetails): void {
                $personalDetails->dateOfBirth = self::getTaskFixtureData('dateOfBirth');
                $personalDetails->bsnCensored = self::getTaskFixtureData('bsnCensored');
                $personalDetails->address = TaskAddress::newInstanceWithVersion(1, static function (TaskAddress $address): void {
                    $address->postalCode = self::getTaskFixtureData('postalCode');
                    $address->houseNumber = self::getTaskFixtureData('houseNumber');
                    $address->houseNumberSuffix = self::getTaskFixtureData('houseNumberSuffix');
                });
            });
            $task->general = General::newInstanceWithVersion(1, static function (General $general): void {
                $general->lastname = self::getTaskFixtureData('lastname');
                $general->phone = self::getTaskFixtureData('phone');
            });
        };
    }

    private static function getTaskFixtureData(string $key): string|DateTimeInterface
    {
        $data = [
            'dateOfBirth' => CarbonImmutable::createFromFormat('Y-m-d', '1950-01-01'),
            'lastname' => 'Doe',
            'bsnCensored' => '******286',
            'postalCode' => '9999XX',
            'houseNumber' => '01',
            'houseNumberSuffix' => 'a',
            'phone' => '+31 71 1880731',
        ];

        assert(isset($data[$key]), new RuntimeException(sprintf('No task fixture data for key "%s"', $key)));

        return $data[$key];
    }
}
