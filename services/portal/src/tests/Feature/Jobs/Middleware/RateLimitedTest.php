<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Middleware;

use App\Jobs\ExportCaseToOsiris;
use App\Jobs\Middleware\RateLimited;
use App\Jobs\RateLimited\RabbitMQJob;
use App\Models\Enums\Osiris\CaseExportType;
use AssertionError;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockCallable;
use TypeError;

use function sprintf;

class RateLimitedTest extends FeatureTestCase
{
    public function testHandleHitsLimiterIfRateLimitNotReached(): void
    {
        $this->mockRateLimiter(false);
        $next = Mockery::mock(MockCallable::class);
        $job = $this->mockJob();

        $next->expects('__invoke');

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->handle($job, $next);
    }

    public function testHandleIsIdleIfJobIsSynchronous(): void
    {
        $this->mockRateLimiter(true);
        $next = Mockery::mock(MockCallable::class);

        $mockQueueJob = $this->getMockBuilder(SyncJob::class)
            ->disableOriginalConstructor()
            ->getMock();
        $job = $this->mockJob($mockQueueJob);

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $this->assertFalse($rateLimited->handle($job, $next));
    }

    public function testHandleIsIdleIfShouldReleaseIsFalse(): void
    {
        $this->mockRateLimiter(true);
        $next = Mockery::mock(MockCallable::class);

        $mockQueueJob = $this->mockRateLimitableJob();
        $job = $this->mockJob($mockQueueJob);

        $mockQueueJob->expects($this->never())->method('postpone');

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->shouldRelease = false;
        $this->assertFalse($rateLimited->handle($job, $next));
    }

    public function testHandleFailsIfJobIsNotRateLimitable(): void
    {
        $this->mockRateLimiter(true);
        $next = Mockery::mock(MockCallable::class);
        $job = $this->mockJob();

        $this->expectException(AssertionError::class);

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->handle($job, $next);
    }

    public function testHandlePostponesJobIfRateLimitIsReached(): void
    {
        $this->mockRateLimiter(true, $this->faker->numberBetween(10, 60));
        $next = Mockery::mock(MockCallable::class);

        $mockQueueJob = $this->getMockBuilder(RabbitMQJob::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['postpone'])
            ->getMock();
        $job = $this->mockJob($mockQueueJob);

        $mockQueueJob->expects($this->once())
            ->method('postpone');

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $this->assertTrue($rateLimited->handle($job, $next));
    }

    public function testHandleThrowsExceptionIfNotObject(): void
    {
        $this->mockRateLimiter(null);
        $next = Mockery::mock(MockCallable::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an object. Got: string');

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->handle('foobar', static fn () => $next);
    }

    public function testHandleThrowsExceptionIfNotUsingTrait(): void
    {
        $this->mockRateLimiter(null);
        $next = Mockery::mock(MockCallable::class);
        $missingTrait = new class {
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('%s requires %s to have %s trait', RateLimited::class, $missingTrait::class, InteractsWithQueue::class),
        );

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->handle($missingTrait, static fn () => $next);
    }

    public function testHandleThrowsExceptionIfNotImplementingInterface(): void
    {
        $this->mockRateLimiter(null);
        $next = Mockery::mock(MockCallable::class);
        $missingInterface = new class {
            use InteractsWithQueue;
        };

        $this->expectException(TypeError::class);

        $next->expects('__invoke')
            ->never();

        $rateLimited = new RateLimited($this->faker->word());
        $rateLimited->handle($missingInterface, static fn () => $next);
    }

    private function mockRateLimiter(?bool $tooManyAttempts, ?int $availableIn = null): void
    {
        Event::fake();

        $limitPerMinute = $this->faker->numberBetween(15, 150);
        $limit = Limit::perMinute($limitPerMinute);
        $hitCount = $this->faker->numberBetween(0, $limitPerMinute);

        $this->mock(
            RateLimiter::class,
            static function (MockInterface $mock) use ($limit, $hitCount, $tooManyAttempts, $availableIn): void {
                $mock->expects('limiter')->andReturn(static fn () => $limit);

                $tooManyAttempts !== null
                    ? $mock->expects('tooManyAttempts')->andReturn($tooManyAttempts)
                    : $mock->expects('tooManyAttempts')->never();

                $tooManyAttempts !== false
                    ? $mock->expects('hit')->never()
                    : $mock->expects('hit')->andReturn($hitCount);

                $availableIn !== null
                    ? $mock->expects('availableIn')->andReturn($availableIn)
                    : $mock->expects('availableIn')->never();
            },
        );
    }

    private function mockJob(?Job $queueJob = null): ExportCaseToOsiris
    {
        if ($queueJob === null) {
            /** @var MockObject&Job $queueJob */
            $queueJob = $this->getMockBuilder(Job::class)
                ->getMock();
        }

        $job = new ExportCaseToOsiris($this->faker->uuid(), $this->faker->randomElement(CaseExportType::cases()));
        $job->setJob($queueJob);

        return $job;
    }

    private function mockRateLimitableJob(): RabbitMQJob&MockObject
    {
        return $this->getMockBuilder(RabbitMQJob::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['postpone', 'getQueue'])
            ->getMock();
    }
}
