<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\LimitChecker;
use Countable;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Tests\Unit\UnitTestCase;

use function array_intersect;
use function count;
use function is_array;

class LimitCheckerTest extends UnitTestCase
{
    private LoggerInterface&MockInterface $log;

    protected function setUp(): void
    {
        $this->log = Mockery::spy(LoggerInterface::class);
    }

    #[DataProvider('smallResultSetData')]
    public function testItDoesNotLogAWarning(array|Collection $resultSet): void
    {
        $this->getClassWithLimitChecker()->do($this->log, $resultSet, 50, 40);

        $this->log->shouldNotHaveReceived('warning');
    }

    #[DataProvider('largeResultSetData')]
    public function testItLogsAWarning(array|Collection $resultSet): void
    {
        $this->getClassWithLimitChecker()->do($this->log, $resultSet, $hardLimit = 50, $softLimit = 40);

        $this->log
            ->shouldHaveReceived('warning')
            ->with(
                'The number of results returned from the query is reaching or already reached the set limit.',
                Mockery::on(static function ($arg) use ($hardLimit, $softLimit, $resultSet) {
                    if (!is_array($arg)) {
                        return false;
                    }

                    $shouldHaveBeen = [
                        'hardLimit' => $hardLimit,
                        'softLimit' => $softLimit,
                        'returnedResults' => count($resultSet),
                    ];

                    return array_intersect($shouldHaveBeen, $arg) === $shouldHaveBeen;
                }),
            );
    }

    public static function smallResultSetData(): array
    {
        $resultSet = Collection::range(1, 10);

        return [
            'resultSet as array' => ['resultSet' => $resultSet->toArray()],
            'resultSet as Collection' => ['resultSet' => $resultSet],
        ];
    }

    public static function largeResultSetData(): array
    {
        $resultSet = Collection::range(1, 45);

        return [
            'resultSet as array' => ['resultSet' => $resultSet->toArray()],
            'resultSet as Collection' => ['resultSet' => $resultSet],
        ];
    }

    private function getClassWithLimitChecker(): object
    {
        return new class ($this->log) {
            use LimitChecker;

            public function __construct(private readonly LoggerInterface $log)
            {
            }

            public function do(
                LoggerInterface $log,
                array|Countable $resultSet,
                ?int $hardLimit = null,
                ?int $softLimit = null,
                int $backTraceAmount = 1,
            ): void {
                $this->limitChecker($log, $resultSet, $hardLimit, $softLimit, $backTraceAmount);
            }
        };
    }
}
