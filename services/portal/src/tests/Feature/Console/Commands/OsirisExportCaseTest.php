<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use Generator;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;
use ValueError;

use function implode;
use function sprintf;

#[Group('osiris')]
#[Group('osiris-case-export')]
final class OsirisExportCaseTest extends FeatureTestCase
{
    #[DataProvider('provideCaseExportTypeOptions')]
    public function testExportToCaseJobIsDispatchedToQueueIfEnabled(CaseExportType $caseExportType, bool $exportEnabled): void
    {
        Queue::fake();
        ConfigHelper::setFeatureFlag('osiris_send_case_enabled', $exportEnabled);

        $this->runCommand($this->faker->uuid(), $caseExportType->value, ['--queue']);

        $exportEnabled
            ? Queue::assertPushed(static fn (ExportCaseToOsiris $job) => $job->connection !== 'sync')
            : Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    #[DataProvider('provideCaseExportTypeOptions')]
    public function testExportToCaseJobIsDispatchedSynchronouslyIfEnabled(CaseExportType $caseExportType, bool $exportEnabled): void
    {
        Queue::fake();
        ConfigHelper::setFeatureFlag('osiris_send_case_enabled', $exportEnabled);

        $this->runCommand($this->faker->uuid(), $caseExportType->value);

        $exportEnabled
            ? Queue::assertPushed(static fn (ExportCaseToOsiris $job) => $job->connection === 'sync')
            : Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testInvalidStrategyOptionThrowsException(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        $this->expectException(ValueError::class);

        $this->runCommand($this->faker->uuid(), 'foobar', ['--queue']);
    }

    public static function provideCaseExportTypeOptions(): Generator
    {
        yield 'type `initial` and export enabled' => [CaseExportType::INITIAL_ANSWERS, true];
        yield 'type `initial` but export disabled' => [CaseExportType::INITIAL_ANSWERS, false];
        yield 'type `definitive` and export enabled' => [CaseExportType::DEFINITIVE_ANSWERS, true];
        yield 'type `definitive` but export disabled' => [CaseExportType::DEFINITIVE_ANSWERS, false];
    }

    private function runCommand(string $caseUuid, string $caseExportType, array $options = []): void
    {
        $this->artisan(
            sprintf('osiris:case-export %s %s %s', $caseUuid, $caseExportType, implode(' ', $options)),
        );
    }
}
