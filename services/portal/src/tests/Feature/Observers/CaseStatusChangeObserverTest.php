<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Repositories\CaseStatusHistoryRepository;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

class CaseStatusChangeObserverTest extends FeatureTestCase
{
    public function testSavingIsIdleIfCaseIsNew(): void
    {
        $this->mock(
            CaseStatusHistoryRepository::class,
            static fn (MockInterface $mock) => $mock->expects('create')->never(),
        );
        $this->createCase();
    }

    public function testSavingIsIdleIfCaseStatusDidNotChange(): void
    {
        $case = $this->createCase(['bco_phase' => BCOPhase::phase1()]);
        $this->mock(
            CaseStatusHistoryRepository::class,
            static fn (MockInterface $mock) => $mock->expects('create')->never(),
        );
        $case->bco_phase = BCOPhase::phase2();
        $case->save();
    }

    public function testSavingIsExecutedIfCaseStatusChangedViaModel(): void
    {
        $case = $this->createCase(['bco_status' => BCOStatus::draft()]);
        $this->mock(
            CaseStatusHistoryRepository::class,
            static fn (MockInterface $mock) => $mock->expects('create')
        );
        $case->bco_status = BCOStatus::open();
        $case->save();
    }
}
