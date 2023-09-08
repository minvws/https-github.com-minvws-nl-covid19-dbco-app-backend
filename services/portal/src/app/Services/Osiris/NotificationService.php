<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisNotification;
use App\Models\StatusIndexContactTracing;

class NotificationService
{
    public static function isIndexContactTracingCompletedForCase(EloquentCase $case): bool
    {
        if ($case->status_index_contact_tracing === null) {
            return false;
        }

        $statusIndexContactTracing = StatusIndexContactTracing::fromString($case->status_index_contact_tracing->value);
        return $statusIndexContactTracing->isCompleted();
    }

    /**
     * Is either a pre-entry or final notification required
     */
    public static function isOsirisNotificationRequiredForCase(EloquentCase $case): bool
    {
        if (self::isOsirisNotificationMissingForCase($case)) {
            return true;
        }

        return self::isOsirisFinalNotificationNeededForCase($case);
    }

    public static function isOsirisNotificationMissingForCase(EloquentCase $case): bool
    {
        return $case->osirisNotifications()->count() === 0;
    }

    public static function isOsirisNotificationUpToDateWithCase(EloquentCase $case): bool
    {
        if (self::isOsirisNotificationMissingForCase($case)) {
            return false;
        }

        /** @var OsirisNotification $osirisNotification */
        $osirisNotification = $case->osirisNotifications()->latest('notified_at')->first();
        return $osirisNotification->notified_at->isAfter($case->updatedAt);
    }

    public static function isOsirisFinalNotificationNeededForCase(EloquentCase $case): bool
    {
        return self::isIndexContactTracingCompletedForCase($case) && !self::isOsirisNotificationUpToDateWithCase($case);
    }
}
