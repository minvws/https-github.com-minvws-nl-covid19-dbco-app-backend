<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisNotification;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

final readonly class DbCaseOsirisNotificationRepository implements CaseOsirisNotificationRepository
{
    public function __construct(
        #[Config('services.osiris.retry_from_date')]
        private string $retryFromDate,
    ) {
    }

    public function getUpdatedCasesWithoutRecentOsirisNotification(): Collection
    {
        return EloquentCase::query()
            ->select('covidcase.*')
            ->leftJoin('osiris_notification', static function (JoinClause $query): void {
                $query->on('osiris_notification.case_uuid', '=', 'covidcase.uuid')
                    ->where('osiris_notification.notified_at', '>=', DB::raw('covidcase.updated_at'));
            })
            ->whereNull('covidcase.hpzone_number')
            ->where('covidcase.created_at', '>', $this->retryFromDate)
            ->where('covidcase.updated_at', '>', $this->retryFromDate)
            ->groupBy('covidcase.uuid')
            ->havingRaw('COUNT(`osiris_notification`.`uuid`) = 0')
            ->orderBy('covidcase.created_at')
            ->get();
    }

    public function findRetryableDeletedCases(): Collection
    {
        return EloquentCase::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->with('osirisNotifications')
            ->where('covidcase.created_at', '>', $this->retryFromDate)
            ->whereNull('hpzone_number')
            ->whereNotNull('covidcase.deleted_at')
            ->orderBy('covidcase.deleted_at')
            ->get();
    }

    public function findLatestDeletedStatusNotification(EloquentCase $case): ?OsirisNotification
    {
        return $case->osirisNotifications()
            ->where('osiris_status', '=', SoapMessageBuilder::NOTIFICATION_STATUS_DELETED)
            ->get()
            ->sortByDesc(static fn (OsirisNotification $item) => $item->notified_at)
            ->first();
    }
}
