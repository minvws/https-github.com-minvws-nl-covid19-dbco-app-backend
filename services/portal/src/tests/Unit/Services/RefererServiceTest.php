<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\RefererService;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\Unit\UnitTestCase;

class RefererServiceTest extends UnitTestCase
{
    #[DataProvider('dpOriginatesFromCovidCaseOverviewPlannerPage')]
    public function testOriginatesFromCovidCaseOverviewPlannerPage(bool $expected, string $referer): void
    {
        $headers = Mockery::mock(HeaderBag::class);
        $headers->expects('get')->with('referer')->andReturn($referer);

        $request = new Request();
        $request->headers = $headers;

        $this->assertSame($expected, RefererService::originatesFromCovidCaseOverviewPlannerPage($request));
    }

    public static function dpOriginatesFromCovidCaseOverviewPlannerPage(): array
    {
        return [
            [true, 'http://http://localhost:8084/planner'],
            [false, 'http://http://localhost:8084/cases'],
        ];
    }
}
