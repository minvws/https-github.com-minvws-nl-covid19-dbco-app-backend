<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PolicyVersionCreated;
use App\Repositories\Policy\CalendarItemPopulator;
use App\Repositories\Policy\CalendarViewPopulator;
use App\Repositories\Policy\PolicyGuidelinePopulator;
use App\Repositories\Policy\RiskProfilePopulator;
use Illuminate\Database\Connection;

final class PopulatePolicyVersion
{
    public function __construct(
        private readonly Connection $db,
        private readonly PolicyGuidelinePopulator $policyGuidelinePopulator,
        private readonly RiskProfilePopulator $riskProfilePopulator,
        private readonly CalendarItemPopulator $calendarItemPopulator,
        private readonly CalendarViewPopulator $calendarViewPopulator,
    )
    {
    }

    public function handle(PolicyVersionCreated $event): void
    {
        $this->db->transaction(function () use ($event): void {
            $this->policyGuidelinePopulator->populate($event->policyVersion);
            $this->riskProfilePopulator->populate($event->policyVersion);
            $this->calendarItemPopulator->populate($event->policyVersion);
            $this->calendarViewPopulator->populate($event->policyVersion);
        });
    }
}
