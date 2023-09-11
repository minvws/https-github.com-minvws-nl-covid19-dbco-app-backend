<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Timeline;

use App\Models\Eloquent\Timeline;
use App\Services\Timeline\TimelineService;
use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

use function app;

class TimelineServiceTest extends FeatureTestCase
{
    public function testAddToTimeline(): void
    {
        /** @var TimelineService $timelineService */
        $timelineService = app(TimelineService::class);

        $user = $this->createUser();

        // because of CaseListAuthScope
        $this->be($user);

        $case = $this->createCaseForUser($user);
        $assignment = $this->createAssignmentHistoryForCase(
            $case,
            ['assigned_user_uuid' => $user->uuid, 'assigned_at' => CarbonImmutable::now()],
        );

        $timelineService->addToTimeline($assignment);

        $callToAction = $this->createCallToAction();
        $callToActionResource = $this->createResourceForCallToAction($callToAction);
        $this->createChoreForCaseAndOrganisation($case, $user->getOrganisation(), [
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
        ]);

        $timelineService->addToTimeline($callToAction);

        $retrievedTimelines = $timelineService->getTimeline($case);

        $this->assertSame(3, $retrievedTimelines->count());

        /** @var Timeline $timeline */
        foreach ($retrievedTimelines as $timeline) {
            $this->assertSame($case->uuid, $timeline->case_uuid);
        }
    }
}
