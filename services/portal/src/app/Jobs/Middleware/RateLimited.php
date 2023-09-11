<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use App\Events\RateLimiter\RateLimiterHit;
use App\Jobs\RateLimited\RateLimitable;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Queue\Middleware\RateLimited as BaseRateLimited;
use stdClass;
use Webmozart\Assert\Assert;

use function assert;
use function sprintf;

final class RateLimited extends BaseRateLimited
{
    /**
     * Replicates the parent method for the most part, but instead of releasing the job,
     * it calls the custom postpone method to allow for different processing of the job.
     *
     * @inheritdoc
     * @see BaseRateLimited::handleJob()
     * @see RateLimitable::postpone()
     *
     * @param array<stdClass> $limits
     */
    protected function handleJob($job, $next, array $limits): mixed
    {
        $queueJob = $this->getQueueJob($job);

        foreach ($limits as $limit) {
            Assert::propertyExists($limit, 'key');
            Assert::propertyExists($limit, 'maxAttempts');
            Assert::propertyExists($limit, 'decayMinutes');

            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                RateLimiterHit::dispatch($limit->maxAttempts + 1, $this->limiterName);

                if (!$this->shouldPostpone($queueJob)) {
                    return false;
                }

                assert($queueJob instanceof RateLimitable);
                $queueJob->postpone($this->getTimeUntilNextRetry($limit->key));

                return true;
            }

            $hitCount = $this->limiter->hit($limit->key, $limit->decayMinutes * 60);

            RateLimiterHit::dispatch($hitCount, $this->limiterName);
        }

        return $next($job);
    }

    private function getQueueJob(mixed $job): Job
    {
        Assert::object($job);
        Assert::propertyExists(
            $job,
            'job',
            sprintf('%s requires %s to have %s trait', self::class, $job::class, InteractsWithQueue::class),
        );
        Assert::implementsInterface($job->job, Job::class);

        return $job->job;
    }

    private function shouldPostpone(Job $queueJob): bool
    {
        return !($queueJob instanceof SyncJob) && $this->shouldRelease;
    }
}
