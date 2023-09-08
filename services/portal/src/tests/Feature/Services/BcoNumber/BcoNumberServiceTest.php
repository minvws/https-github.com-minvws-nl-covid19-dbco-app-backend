<?php

declare(strict_types=1);

namespace Tests\Feature\Services\BcoNumber;

use App\Models\Eloquent\BcoNumber;
use App\Services\BcoNumber\BcoNumberException;
use App\Services\BcoNumber\BcoNumberGenerator;
use App\Services\BcoNumber\BcoNumberService;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\FeatureTestCase;

class BcoNumberServiceTest extends FeatureTestCase
{
    public function testRetriesAfterFailure(): void
    {
        /** @var BcoNumberGenerator&MockObject $bcoNumberGenerator */
        $bcoNumberGenerator = $this->mock(BcoNumberGenerator::class, static function (MockInterface $mock): void {
            $mock->expects('buildCode')
                ->times(3)
                ->andReturn('123-123-123', '111-222-333', '111-111-111');
        });

        BcoNumber::create(['bco_number' => '123-123-123']);
        BcoNumber::create(['bco_number' => '111-222-333']);

        $numberService = new BcoNumberService(20, $bcoNumberGenerator);
        $createdNumber = $numberService->makeUniqueNumber();

        $this->assertSame('111-111-111', $createdNumber->bco_number);
    }

    public function testThrowsExceptionAfterMaxRetries(): void
    {
        /** @var BcoNumberGenerator&MockObject $bcoNumberGenerator */
        $bcoNumberGenerator = $this->mock(BcoNumberGenerator::class, static function (MockInterface $mock): void {
            $mock->expects('buildCode')
                ->times(2)
                ->andReturn('123-123-123', '111-222-333');
        });

        BcoNumber::create(['bco_number' => '123-123-123']);
        BcoNumber::create(['bco_number' => '111-222-333']);

        $numberService = new BcoNumberService(2, $bcoNumberGenerator);

        $this->expectException(BcoNumberException::class);
        $numberService->makeUniqueNumber();
    }
}
