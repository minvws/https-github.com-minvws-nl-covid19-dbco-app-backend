<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fragments\Context\General;

use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

class GeneralMomentsTimeFormattingTest extends FeatureTestCase
{
    public function testCorrectlySavesWithInvalidTime(): void
    {
        $today = CarbonImmutable::today();
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $weekBeforeToday = $today->subDays(7);

        $this->createMomentForContext($context, [
            'day' => $weekBeforeToday->format('Y-m-d'),
            'start_time' => '-10:00:00',
            'end_time' => '-00:00:01',
        ]);

        $context->save();
        $context->refresh();

        $this->assertCount(1, $context->general->moments);
        $this->assertEquals($weekBeforeToday->format('Y-m-d'), $context->general->moments[0]->day->format('Y-m-d'));
        $this->assertEquals(null, $context->general->moments[0]->startTime);
        $this->assertEquals(null, $context->general->moments[0]->endTime);
    }

    public function testCorrectlySavesWithNullTime(): void
    {
        $today = CarbonImmutable::today();
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $weekBeforeToday = $today->subDays(7);

        $this->createMomentForContext($context, [
            'day' => $weekBeforeToday->format('Y-m-d'),
            'start_time' => null,
            'end_time' => null,
        ]);

        $context->save();
        $context->refresh();

        $this->assertCount(1, $context->general->moments);
        $this->assertEquals($weekBeforeToday->format('Y-m-d'), $context->general->moments[0]->day->format('Y-m-d'));
        $this->assertEquals(null, $context->general->moments[0]->startTime);
        $this->assertEquals(null, $context->general->moments[0]->endTime);
    }

    public static function provideTimes(): array
    {
        return [
            'valid times H:i:s' => [
                '10:00:00',
                '11:00:00',
            ],
            'valid times H:i' => [
                '10:00',
                '11:00',
            ],
        ];
    }

    /** @dataProvider provideTimes */
    public function testCorrectlySavesWithValidDate(string $startTime, string $endTime): void
    {
        $today = CarbonImmutable::today();
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $weekBeforeToday = $today->subDays(7);

        $this->createMomentForContext($context, [
            'day' => $weekBeforeToday->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $context->general->moments = (array) $context->general->moments;
        $context->save();
        $context->refresh();

        $this->assertCount(1, $context->general->moments);
        $this->assertEquals($weekBeforeToday->format('Y-m-d'), $context->general->moments[0]->day->format('Y-m-d'));
        $this->assertEquals(
            CarbonImmutable::parse($startTime)->format('H:i'),
            $context->general->moments[0]->startTime,
        );
        $this->assertEquals(CarbonImmutable::parse($endTime)->format('H:i'), $context->general->moments[0]->endTime);
    }
}
