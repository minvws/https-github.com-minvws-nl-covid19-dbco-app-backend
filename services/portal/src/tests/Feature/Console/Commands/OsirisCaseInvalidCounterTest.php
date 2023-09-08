<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\CovidCase\Index;
use App\Models\Versions\CovidCase\Index\IndexV1;
use Carbon\CarbonImmutable;
use Illuminate\Testing\PendingCommand;
use Tests\Feature\FeatureTestCase;

class OsirisCaseInvalidCounterTest extends FeatureTestCase
{
    public function testOutputDisplaysCounters(): void
    {
        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('osiris:case-invalid-counter', [
            'start-date' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
            'end-date' => $this->faker->dateTimeBetween('-1 month')->format('d-m-Y'),
        ]);

        $artisan->assertOk();

        $artisan->expectsOutput('total cases: 0')
            ->expectsOutput('valid cases: 0')
            ->expectsOutput('invalid cases: 0');
    }

    public function testWithOnlyOneValidCase(): void
    {
        $this->createCase([
            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
        ]);

        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('osiris:case-invalid-counter', [
            'start-date' => CarbonImmutable::now()->subMonths(2)->format('d-m-Y'),
            'end-date' => CarbonImmutable::now()->subMonths(1)->format('d-m-Y'),
        ]);

        $artisan->assertOk();

        $artisan->expectsOutput('total cases: 1')
            ->expectsOutput('valid cases: 1')
            ->expectsOutput('invalid cases: 0');
    }

    public function testWithOnlyOneInvalidCase(): void
    {
        $this->createCase([
            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
            'index' => Index::newInstanceWithVersion(1, static function (IndexV1 $indexV1): void {
                $indexV1->dateOfBirth = CarbonImmutable::createStrict(1900);
            }),
        ]);

        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('osiris:case-invalid-counter', [
            'start-date' => CarbonImmutable::now()->subMonths(2)->format('d-m-Y'),
            'end-date' => CarbonImmutable::now()->subMonths(1)->format('d-m-Y'),
        ]);

        $artisan->assertOk();

        $artisan->expectsOutput('total cases: 1')
            ->expectsOutput('valid cases: 0')
            ->expectsOutput('invalid cases: 1')
            ->expectsOutput('- dateOfBirth_before_19060101: 1');
    }

//    public function testWithMultiple(): void
//    {
//        // valid cases
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//        ]);
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//        ]);
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//        ]);
//
//        // dateOfBirth_before_19060101
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//            'index' => Index::newInstanceWithVersion(1, function (IndexV1 $indexV1) {
//                $indexV1->dateOfBirth = CarbonImmutable::create(1900);
//            }),
//        ]);
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//            'index' => Index::newInstanceWithVersion(1, function (IndexV1 $indexV1) {
//                $indexV1->dateOfBirth = CarbonImmutable::create(1900);
//            }),
//        ]);
//
//        // deceasedAt_before_hospitalAdmittedAt
//        $this->createCase([
//            'created_at' => $this->faker->dateTimeBetween('-2 month', '-1 month')->format('d-m-Y'),
//            'deceased' => Deceased::newInstanceWithVersion(1, function (DeceasedV1 $deceasedV1) {
//                $deceasedV1->isDeceased = YesNoUnknown::yes();
//                $deceasedV1->deceasedAt = CarbonImmutable::now()->subYear();
//            }),
//            'hospital' => Hospital::newInstanceWithVersion(1, function (HospitalV1 $hospitalV1) {
//                $hospitalV1->isAdmitted = YesNoUnknown::yes();
//                $hospitalV1->admittedAt = CarbonImmutable::now()->addYear();
//            }),
//        ]);
//
//        /** @var PendingCommand $artisan */
//        $artisan = $this->artisan('osiris:case-invalid-counter', [
//            'start-date' => CarbonImmutable::now()->subMonths(2)->format('d-m-Y'),
//            'end-date' => CarbonImmutable::now()->subMonths(1)->format('d-m-Y'),
//        ]);
//
//        $artisan->assertOk();
//
//        $artisan->expectsOutput('total cases: 6')
//            ->expectsOutput('valid cases: 3')
//            ->expectsOutput('invalid cases: 3')
//            ->expectsOutput('- dateOfBirth_before_19060101: 2')
//            ->expectsOutput('- deceasedAt_before_hospitalAdmittedAt: 1');
//    }
}
