<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Events\RateLimiter\RateLimiterHit;
use App\Helpers\Config;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\SoapCaseExportRepository;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use App\ValueObjects\OsirisNumber;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;
use Webmozart\Assert\Assert;

class RateLimitedExportCaseToOsirisTest extends FeatureTestCase
{
    public function testItIsHandledIfRateLimitNotReached(): void
    {
        Event::fake([RateLimiterHit::class]);

        $case = $this->createCaseExportableToOsiris();
        $mockResult = new CaseExportResult(
            new OsirisNumber($this->faker->randomNumber(2)),
            QuestionnaireVersion::V10->value,
            $case->caseId,
            $case->uuid,
        );

        $limitPerMinute = $this->faker->numberBetween(15, 150);
        $limit = Limit::perMinute($limitPerMinute);
        $hitCount = $this->faker->numberBetween(0, $limitPerMinute);

        $this->mock(RateLimiter::class, static function (MockInterface $mock) use ($limit, $hitCount): void {
            $mock->expects('limiter')->andReturn(static fn () => $limit);
            $mock->expects('tooManyAttempts')->andReturn(false);
            $mock->expects('hit')->andReturn($hitCount);
        });
        $this->mock(SoapCaseExportRepository::class, static function (MockInterface $mock) use ($mockResult): void {
            $mock->expects('exportCase')
                ->andReturns($mockResult);
        });

        $this->pushOnQueue($case);

        Event::assertDispatched(static function (RateLimiterHit $event) use ($hitCount): bool {
            Assert::eq($event->hitCount, $hitCount);
            Assert::eq($event->limiterName, Config::string('services.osiris.rate_limit.rate_limiter_key'));

            return true;
        });
    }

    public function testItIsNotHandledIfRateLimitIsReached(): void
    {
        Event::fake([RateLimiterHit::class]);

        $case = $this->createCase();

        $limitPerMinute = $this->faker->numberBetween(15, 150);
        $limit = Limit::perMinute($limitPerMinute);

        $this->mock(RateLimiter::class, static function (MockInterface $mock) use ($limit): void {
            $mock->expects('limiter')->andReturn(static fn () => $limit);
            $mock->expects('tooManyAttempts')->andReturn(true);
            $mock->expects('hit')->never();
        });
        $this->mock(SoapCaseExportRepository::class, static function (MockInterface $mock): void {
            $mock->expects('exportCase')->never();
        });

        $this->pushOnQueue($case);

        Event::assertDispatched(static function (RateLimiterHit $event) use ($limitPerMinute): bool {
            Assert::eq($event->hitCount, $limitPerMinute + 1);
            Assert::eq($event->limiterName, Config::string('services.osiris.rate_limit.rate_limiter_key'));

            return true;
        });
    }

    private function pushOnQueue(EloquentCase $case): void
    {
        $job = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));
        Queue::pushOn(Config::string('services.osiris.case_export_job.queue_name'), $job);
    }
}
