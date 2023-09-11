<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Throwable;

use function toDate;

class HelpersTest extends TestCase
{
    #[DataProvider('toDateDataProvider')]
    public function testToDate(?string $modifierAmount, ?string $modifierName, string $expectedResult): void
    {
        $now = CarbonImmutable::createStrict(2022, 1, 1);

        $result = $modifierName === null ? toDate($now, $modifierAmount) : toDate($now, $modifierAmount, $modifierName);
        $this->assertEquals($expectedResult, $result);
    }

    public static function toDateDataProvider(): array
    {
        return [
            [null, null, 'zaterdag 1 januari'],
            ['1', null, 'zondag 2 januari'],
            ['+1', null, 'zondag 2 januari'],
            ['+1', 'days', 'zondag 2 januari'],
            ['+1', 'week', 'zaterdag 8 januari'],
            ['+10', 'days', 'dinsdag 11 januari'],
            ['+10', 'weeks', 'zaterdag 12 maart'],
            ['-1', 'days', 'vrijdag 31 december'],
            ['-1', 'week', 'zaterdag 25 december'],
        ];
    }

    public function testToDateFails(): void
    {
        $this->expectException(Throwable::class);
        toDate(CarbonImmutable::now(), 'foo');
    }
}
