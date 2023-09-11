<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class RemoveLabelsAndPriorityFromArchivedCasesTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        $this->runCommand(0);
    }

    #[DataProvider('bcoStatusDataProvider')]
    public function testCommandRemovesPriority(
        BCOStatus $BCOStatus,
        bool $shouldBeRemoved,
    ): void {
        $case = $this->createCase([
            'bco_status' => $BCOStatus,
            'priority' => Priority::veryHigh(),
        ]);

        // add some cases that should not be touched
        $this->createCase(['bco_status' => BCOStatus::open()]);
        $this->createCase(['bco_status' => BCOStatus::completed()]);

        $this->runCommand($shouldBeRemoved ? 1 : 0);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'priority' => $shouldBeRemoved ? 0 : 3,
        ]);
    }

    #[DataProvider('bcoStatusDataProvider')]
    public function testCommandRemovesLabels(
        BCOStatus $BCOStatus,
        bool $shouldBeRemoved,
    ): void {
        $case = $this->createCase(['bco_status' => $BCOStatus]);
        $caseLabel = $this->createCaseLabel();
        $case->caseLabels()->attach($caseLabel);

        // add some cases that should not be touched
        $nonTouchedCase = $this->createCase(['bco_status' => BCOStatus::open()]);
        $nonTouchedCase->caseLabels()->attach($caseLabel);
        $this->createCase(['bco_status' => BCOStatus::completed()]);

        $this->runCommand($shouldBeRemoved ? 1 : 0);

        $expectedDatabaseData = [
            'case_uuid' => $case->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ];

        if ($shouldBeRemoved) {
            $this->assertDatabaseMissing('case_case_label', $expectedDatabaseData);
        } else {
            $this->assertDatabaseHas('case_case_label', $expectedDatabaseData);
        }
    }

    #[DataProvider('bcoStatusDataProvider')]
    public function testCommandRemovesPriorityAndLabel(
        BCOStatus $BCOStatus,
        bool $shouldBeRemoved,
    ): void {
        $caseWithPriority = $this->createCase([
            'bco_status' => $BCOStatus,
            'priority' => Priority::veryHigh(),
        ]);
        $caseWithCaseLabel = $this->createCase(['bco_status' => $BCOStatus]);
        $caseLabel = $this->createCaseLabel();
        $caseWithCaseLabel->caseLabels()->attach($caseLabel);

        // add some cases that should not be touched
        $nonTouchedCase = $this->createCase(['bco_status' => BCOStatus::open()]);
        $nonTouchedCase->caseLabels()->attach($caseLabel);
        $this->createCase(['bco_status' => BCOStatus::open()]);

        $this->runCommand($shouldBeRemoved ? 2 : 0);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $caseWithPriority->uuid,
            'priority' => $shouldBeRemoved ? 0 : 3,
        ]);

        $expectedDatabaseData = [
            'case_uuid' => $caseWithCaseLabel->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ];

        if ($shouldBeRemoved) {
            $this->assertDatabaseMissing('case_case_label', $expectedDatabaseData);
        } else {
            $this->assertDatabaseHas('case_case_label', $expectedDatabaseData);
        }
    }

    public static function bcoStatusDataProvider(): array
    {
        return [
            'draft' => [BCOStatus::draft(), false],
            'open' => [BCOStatus::open(), false],
            'completed' => [BCOStatus::completed(), false],
            'archived' => [BCOStatus::archived(), true],
            'unknown' => [BCOStatus::unknown(), false],
        ];
    }

    public function testCommandSkipsCaseIfPriorityAlreadyNoneAndNoLabels(): void
    {
        $this->createCase([
            'bco_status' => BCOStatus::archived(),
            'priority' => Priority::none(),
        ]);

        $this->runCommand(0);
    }

    private function runCommand(int $expectedCount): void
    {
        $artisan = $this->artisan('cases:remove-label-and-priority-from-archived');
        $artisan->expectsOutput('Removing labels and priority from archived cases...')
            ->expectsOutput(sprintf('Removed labels and priority from %s cases', $expectedCount))
            ->assertExitCode(0)
            ->execute();
    }
}
