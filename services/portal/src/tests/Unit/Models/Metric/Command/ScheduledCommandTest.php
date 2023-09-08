<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\Command;

use App\Models\Metric\Command\ScheduledCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('metric')]
class ScheduledCommandTest extends UnitTestCase
{
    #[DataProvider('statusDataProvider')]
    public function testLabels(string $status): void
    {
        $class = $this->faker->word();

        $metric = ScheduledCommand::$status($class);

        $expectedLabels = [
            'class' => $class,
            'status' => $status,
        ];
        $this->assertEquals($expectedLabels, $metric->getLabels());
    }

    public static function statusDataProvider(): array
    {
        return [
            'before' => ['before'],
            'failure' => ['failure'],
            'success' => ['success'],
        ];
    }
}
