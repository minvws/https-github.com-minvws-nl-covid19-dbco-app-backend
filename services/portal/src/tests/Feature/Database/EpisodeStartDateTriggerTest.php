<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class EpisodeStartDateTriggerTest extends FeatureTestCase
{
    #[DataProvider('provideCaseDatesToCreate')]
    public function testTriggerBeforeInsertSetsEpisodeStartDate(
        ?string $dateOfTestDays,
        ?string $dateOfSymptomOnsetDays,
        string $assertSourceAttribute,
    ): void {
        $case = $this->createCase([
            'created_at' => $this->faker->dateTimeBetween('-2 months'),
            'date_of_test' => $dateOfTestDays ? $this->faker->dateTimeBetween('1 day', $dateOfTestDays) : null,
            'date_of_symptom_onset' => $dateOfSymptomOnsetDays ? $this->faker->dateTimeBetween('1 day', $dateOfSymptomOnsetDays) : null,
        ]);
        $case->refresh();

        $this->assertInstanceOf(DateTimeInterface::class, $case->{$assertSourceAttribute});
        $this->assertEquals($case->{$assertSourceAttribute}->format('Y-m-d'), $case->episode_start_date->format('Y-m-d'));
    }

    public static function provideCaseDatesToCreate(): Generator
    {
        yield 'case having neither `date of test` nor `date of symptom_onset`' => [null, null, 'created_at'];
        yield 'case having `date of test`, but no `date of symptom_onset`' => ['14 days', null, 'date_of_test'];
        yield 'case both `date of test` and `date of symptom onset`' => ['14 days', '14 days', 'date_of_symptom_onset'];
    }

    #[DataProvider('provideCaseDatesToUpdate')]
    public function testTriggerBeforeUpdateSetsEpisodeStartDate(
        bool $unsetDateOfTest,
        bool $unsetDateOfSymptomOnset,
        string $assertSourceAttribute,
    ): void {
        $now = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 month'));
        CarbonImmutable::setTestNow($now);

        $dateOfSymptomOnset = $now->copy()->addDays($this->faker->numberBetween(1, 7));
        $case = $this->createCase([
            'date_of_symptom_onset' => $dateOfSymptomOnset,
            'date_of_test' => $dateOfSymptomOnset->copy()->addDays($this->faker->numberBetween(1, 7)),
        ]);

        if ($unsetDateOfTest) {
            $case->date_of_test = null;
        }
        if ($unsetDateOfSymptomOnset) {
            $case->date_of_symptom_onset = null;
        }
        $case->save();
        $case->refresh();

        $this->assertInstanceOf(DateTimeInterface::class, $case->{$assertSourceAttribute});
        $this->assertEquals($case->{$assertSourceAttribute}->format('Y-m-d'), $case->episode_start_date->format('Y-m-d'));
    }

    public static function provideCaseDatesToUpdate(): Generator
    {
        yield 'existing case unsetting `date of test`' => [true, false, 'date_of_symptom_onset'];
        yield 'existing case unsetting `date of symptom onset`' => [false, true, 'date_of_test'];
        yield 'existing case unsetting both `date of symptom onset` and `date of test`' => [true, true, 'created_at'];
    }
}
