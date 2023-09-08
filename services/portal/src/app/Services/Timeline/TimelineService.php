<?php

declare(strict_types=1);

namespace App\Services\Timeline;

use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\Contracts\TimelineInterface;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Note;
use App\Models\Eloquent\Timeline;
use App\Repositories\TimelineRepository;
use App\Services\Factory\TimelineDtoFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

use function collect;

class TimelineService
{
    private TimelineRepository $timelineRepository;
    private TimelineDtoFactory $dtoFactory;

    public const NOTE = 'note';
    public const CASE_ASSIGNMENT_HISTORY = 'case-assignment-history';

    public function __construct(TimelineRepository $timelineRepository, TimelineDtoFactory $dtoFactory)
    {
        $this->timelineRepository = $timelineRepository;
        $this->dtoFactory = $dtoFactory;
    }

    public function addToTimeline(TimelineInterface $timelineable): void
    {
        $this->timelineRepository->addToTimeline($timelineable);
    }

    /**
     * @return EloquentCollection<int,Timeline>
     */
    public function getTimeline(EloquentCase $case): Collection
    {
        return $this->timelineRepository->getTimeline($case);
    }

    /**
     * @return EloquentCollection<int,Timeline>
     */
    public function getPlannerTimeline(EloquentCase $case): Collection
    {
        return $this->timelineRepository->getTimeline($case, [self::NOTE, self::CASE_ASSIGNMENT_HISTORY]);
    }

    /**
     * @param Collection<Timeline> $timelines
     */
    public function timelinesToDto(Collection $timelines): Collection
    {
        $dtoCollection = collect();
        /** @var Timeline $timeline */
        foreach ($timelines as $timeline) {
            $timelineable = $timeline->timelineable;
            if ($timelineable instanceof Note) {
                $dtoCollection->add($this->dtoFactory->fromNote($timelineable));
            } elseif ($timelineable instanceof CaseAssignmentHistory) {
                $dtoCollection->add($this->dtoFactory->fromCaseAssignmentHistory($timelineable, $timelines));
            } elseif ($timelineable instanceof ExpertQuestion) {
                $dtoCollection->add($this->dtoFactory->fromExpertQuestion($timelineable));
            } elseif ($timelineable instanceof CallToAction) {
                $dtoCollection->add($this->dtoFactory->fromCallToAction($timelineable));
            }
        }

        return $dtoCollection;
    }
}
