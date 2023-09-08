<?php

declare(strict_types=1);

namespace Tests\Feature\Services\SearchHash\EloquentTask\General;

use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('search-hash')]
class GeneralHashTest extends FeatureTestCase
{
    public function testFromTaskWithAllData(): void
    {
        $dateOfBirth = $this->faker->dateTimeBetween();
        $lastname = $this->faker->lastName();
        $phone = $this->faker->phoneNumber();
        $formattedPhone = PhoneFormatter::format($phone);

        $task = EloquentTask::newInstanceWithVersion(4, function (EloquentTask $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });
        $task->personal_details = PersonalDetails::newInstanceWithVersion(
            1,
            static function (PersonalDetails $personalDetails) use ($dateOfBirth): void {
                $personalDetails->dateOfBirth = $dateOfBirth;
            },
        );
        $task->general = General::newInstanceWithVersion(
            1,
            static function (General $general) use ($lastname, $phone): void {
                $general->lastname = $lastname;
                $general->phone = $phone;
            },
        );

        $expectedGeneralHash = new GeneralHash($dateOfBirth, $lastname, $formattedPhone);

        $this->assertEquals($expectedGeneralHash, GeneralHash::fromTask($task));
    }

    public function testFromTaskWithoutPhone(): void
    {
        $dateOfBirth = $this->faker->dateTimeBetween();
        $lastname = $this->faker->lastName();

        $expectedGeneralHash = new GeneralHash($dateOfBirth, $lastname, null);

        $task = EloquentTask::newInstanceWithVersion(4, function (EloquentTask $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });
        $task->personal_details = PersonalDetails::newInstanceWithVersion(
            1,
            static function (PersonalDetails $personalDetails) use ($dateOfBirth): void {
                $personalDetails->dateOfBirth = $dateOfBirth;
            },
        );
        $task->general = General::newInstanceWithVersion(
            1,
            static function (General $general) use ($lastname): void {
                $general->lastname = $lastname;
            },
        );

        $this->assertEquals($expectedGeneralHash, GeneralHash::fromTask($task));
    }

    public function testFromTaskWithoutLastname(): void
    {
        $dateOfBirth = $this->faker->dateTimeBetween();
        $phone = $this->faker->phoneNumber();
        $formattedPhone = PhoneFormatter::format($phone);

        $task = EloquentTask::newInstanceWithVersion(4, function (EloquentTask $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });
        $task->personal_details = PersonalDetails::newInstanceWithVersion(
            1,
            static function (PersonalDetails $personalDetails) use ($dateOfBirth): void {
                $personalDetails->dateOfBirth = $dateOfBirth;
            },
        );
        $task->general = General::newInstanceWithVersion(
            1,
            static function (General $general) use ($phone): void {
                $general->phone = $phone;
            },
        );

        $expectedGeneralHash = new GeneralHash($dateOfBirth, null, $formattedPhone);

        $this->assertEquals($expectedGeneralHash, GeneralHash::fromTask($task));
    }
}
