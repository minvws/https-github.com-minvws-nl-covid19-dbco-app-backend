<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\CaseExportSucceeded;
use App\Models\Eloquent\OsirisNotification;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;

final class CreateOsirisNotification
{
    public function __invoke(CaseExportSucceeded $event): void
    {
        $notification = OsirisNotification::forCaseExport(
            $event->case,
            SoapMessageBuilder::mapToStatus($event->caseExportType),
            $event->caseExportResult->questionnaireVersion,
        );

        $event->case->osirisNotifications()->save($notification);
    }
}
