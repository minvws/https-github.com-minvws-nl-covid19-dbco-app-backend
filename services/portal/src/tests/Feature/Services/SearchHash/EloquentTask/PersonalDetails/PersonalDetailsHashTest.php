<?php

declare(strict_types=1);

namespace Tests\Feature\Services\SearchHash\EloquentTask\PersonalDetails;

use App\Models\Eloquent\EloquentTask;
use App\Models\Task\PersonalDetails;
use App\Models\Task\TaskAddress;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use Closure;
use Faker\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('search-hash')]
class PersonalDetailsHashTest extends FeatureTestCase
{
    /**
     * @param Closure(EloquentTask):void $taskClosure
     */
    #[DataProvider('fromTaskData')]
    public function testFromTaskFoo(
        Closure $taskClosure,
        PersonalDetailsHash $expectedPersonalDetailsHash,
    ): void {
        $this->assertEquals(
            $expectedPersonalDetailsHash,
            PersonalDetailsHash::fromTask($this->newTaskWithTimestamps($taskClosure)),
        );
    }

    public static function fromTaskData(): array
    {
        $faker = Factory::create('nl_NL');

        $fixture = [
            'dateOfBirth' => $faker->dateTimeBetween(),
            'lastThreeDigitsBsn' => (string) $faker->numberBetween(100, 999),
            'postalCode' => $faker->postcode(),
            'houseNumber' => (string) $faker->numberBetween(10, 100),
            'houseNumberSuffix' => $faker->optional()->randomLetter(),
        ];

        return [
            'all data' => [
                'taskClosure' => static function (EloquentTask $task) use ($fixture): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails) use ($fixture): void {
                            $personalDetails->dateOfBirth = $fixture['dateOfBirth'];
                            $personalDetails->bsnCensored = $fixture['lastThreeDigitsBsn'];
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address) use ($fixture): void {
                                    $address->postalCode = $fixture['postalCode'];
                                    $address->houseNumber = $fixture['houseNumber'];
                                    $address->houseNumberSuffix = $fixture['houseNumberSuffix'];
                                },
                            );
                        },
                    );
                },
                'expectedPersonalDetailsHash' => new PersonalDetailsHash(
                    dateOfBirth: $fixture['dateOfBirth'],
                    lastThreeBsnDigits: $fixture['lastThreeDigitsBsn'],
                    postalCode: $fixture['postalCode'],
                    houseNumber: $fixture['houseNumber'],
                    houseNumberSuffix: $fixture['houseNumberSuffix'],
                ),
            ],
            'all data except personalDetails->dateOfBirth' => [
                'taskClosure' => static function (EloquentTask $task) use ($fixture): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails) use ($fixture): void {
                            $personalDetails->bsnCensored = $fixture['lastThreeDigitsBsn'];
                            $personalDetails->address = TaskAddress::newInstanceWithVersion(
                                1,
                                static function (TaskAddress $address) use ($fixture): void {
                                    $address->postalCode = $fixture['postalCode'];
                                    $address->houseNumber = $fixture['houseNumber'];
                                    $address->houseNumberSuffix = $fixture['houseNumberSuffix'];
                                },
                            );
                        },
                    );
                },
                'expectedPersonalDetailsHash' => new PersonalDetailsHash(
                    dateOfBirth: null,
                    lastThreeBsnDigits: $fixture['lastThreeDigitsBsn'],
                    postalCode: $fixture['postalCode'],
                    houseNumber: $fixture['houseNumber'],
                    houseNumberSuffix: $fixture['houseNumberSuffix'],
                ),
            ],
            'only personalDetails->dateOfBirth and personalDetails->bsnCensored' => [
                'taskClosure' => static function (EloquentTask $task) use ($fixture): void {
                    $task->personal_details = PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails) use ($fixture): void {
                            $personalDetails->dateOfBirth = $fixture['dateOfBirth'];
                            $personalDetails->bsnCensored = $fixture['lastThreeDigitsBsn'];
                        },
                    );
                },
                'expectedPersonalDetailsHash' => new PersonalDetailsHash(
                    dateOfBirth: $fixture['dateOfBirth'],
                    lastThreeBsnDigits: $fixture['lastThreeDigitsBsn'],
                    postalCode: null,
                    houseNumber: null,
                    houseNumberSuffix: null,
                ),
            ],
        ];
    }

    protected function newTaskWithTimestamps(Closure $taskClosure): EloquentTask
    {
        $case = EloquentTask::newInstanceWithVersion(4, function (EloquentTask $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });

        $taskClosure($case);

        return $case;
    }
}
