<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\Contracts\TimelineInterface;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Timeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TimelineRepository
{
    public function addToTimeline(TimelineInterface $timelineable): void
    {
        $timeline = new Timeline();
        $timeline->case_uuid = $timelineable->getCaseUuid();
        $timelineable->timeline()->save($timeline);
    }

    /**
     * @return Collection<int,Timeline>
     */
    public function getTimeline(EloquentCase $case, array $types = []): Collection
    {
        $query = Timeline::with([
            'timelineable' => static function (MorphTo $morphTo): void {
                $morphTo->morphWith([
                    CaseAssignmentHistory::class => ['assignedBy'],
                ]);
            }])
            ->where('case_uuid', $case->uuid);

        if (!empty($types)) {
            $query->whereIn('timelineable_type', $types);
        }

        return $query->orderByDesc('created_at')
            ->get();
    }
}
