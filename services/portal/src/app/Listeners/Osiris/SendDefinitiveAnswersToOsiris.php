<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Case\CaseOrganisationUpdated;
use App\Events\Case\CaseUpdatedByPlanner;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use MinVWS\DBCO\Enum\Models\BCOStatus;

final class SendDefinitiveAnswersToOsiris
{
    public function whenCaseIsArchived(CaseOrganisationUpdated|CaseUpdatedByPlanner $event): void
    {
        if ($event->eloquentCase->bcoStatus !== BcoStatus::archived()) {
            return;
        }

        ExportCaseToOsiris::dispatchIfEnabled($event->eloquentCase->uuid, CaseExportType::DEFINITIVE_ANSWERS);
    }
}
