<?php

declare(strict_types=1);

namespace App\Repositories;

use Countable;
use Psr\Log\LoggerInterface;

use function count;
use function debug_backtrace;
use function sprintf;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

trait LimitChecker
{
    protected int $hardLimit = 500;
    protected int $softLimit = 400;

    protected function limitChecker(
        LoggerInterface $log,
        array|Countable $resultSet,
        ?int $hardLimit = null,
        ?int $softLimit = null,
        int $backTraceAmount = 1,
    ): void {
        $hardLimit = $hardLimit ?? $this->hardLimit;
        $softLimit = $softLimit ?? $this->softLimit;

        $count = count($resultSet);
        if ($count < $softLimit) {
            return;
        }

        $call = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backTraceAmount)[$backTraceAmount - 1] ?? [];

        $log->warning('The number of results returned from the query is reaching or already reached the set limit.', [
            'method' => sprintf('%s%s%s', $call['class'] ?? 'UnkownClass', $call['type'] ?? '::', $call['function'] ?? 'UnkownMethod'),
            'hardLimit' => $hardLimit,
            'softLimit' => $softLimit,
            'returnedResults' => $count,
        ]);
    }
}
