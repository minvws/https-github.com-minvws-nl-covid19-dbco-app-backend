<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\CaseIndexAgeCalculatorKeyHelper;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class CaseIndexAgeCalculatorKeyHelperTest extends FeatureTestCase
{
    #[DataProvider('calculatorKeyDataProvider')]
    public function testGetCalculatorKey(string $date, int $expected): void
    {
        $dateTime = CarbonImmutable::createFromFormat('d-m-Y', $date);

        $this->assertEquals($expected, CaseIndexAgeCalculatorKeyHelper::getCalculatorKey($dateTime));
    }

    public static function calculatorKeyDataProvider(): array
    {
        return [
            '1-1-2000' => ['1-1-2000', 1],
            '2-3-2004' => ['2-3-2004', 3],
        ];
    }
}
