<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase;

use App\Models\Eloquent\BcoNumber;
use App\Services\BcoNumber\BcoNumberService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class CaseReportNumberTest extends FeatureTestCase
{
    #[DataProvider('reportNumberDataProvider')]
    public function testReportNumber(?string $hpzoneNumber, ?string $caseId, ?string $expectedReportNumber): void
    {
        // make sure case_id is not set during Eloquent::booted()-method
        $this->mock(BcoNumberService::class, static function (MockInterface $mock): void {
            $mock->allows('makeUniqueNumber')
                ->andReturn(new BcoNumber(['bco_number' => null]));
        });

        $case = $this->createCase([
            'hpzone_number' => $hpzoneNumber,
            'case_id' => $caseId,
        ]);

        $this->assertEquals($expectedReportNumber, $case->getReportNumber());
    }

    public static function reportNumberDataProvider(): array
    {
        return [
            'both missing' => [null, null, null],
            'only hpzoneNumber set' => ['123', null, '123'],
            'only caseId set' => [null, '456', '456'],
            'both set' => ['abc', 'def', 'abc'],
        ];
    }
}
