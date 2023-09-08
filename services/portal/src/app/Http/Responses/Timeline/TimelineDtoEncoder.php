<?php

declare(strict_types=1);

namespace App\Http\Responses\Timeline;

use App\Dto\CallToActionTimelineDto;
use App\Dto\ExpertQuestionTimelineDto;
use App\Dto\TimelineDto;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class TimelineDtoEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if ($value instanceof ExpertQuestionTimelineDto) {
            $container->uuid = $value->getUuid();
            $container->note = $value->getNote();
            $container->title = $value->getTitle();
            $container->author_user = $value->getQuestionUserName();
            $container->time = $value->getTime();
            $container->timelineable_id = $value->getTimelineableId();
            $container->timelineable_type = $value->getTimelineableType();
            $container->answer = $value->getAnswer();
            $container->answer_user = $value->getAnswerUser();
            $container->answer_time = $value->getAnswerTime();

            return;
        }

        if ($value instanceof CallToActionTimelineDto) {
            $container->uuid = $value->getUuid();
            $container->note = $value->getNote();
            $container->title = $value->getTitle();
            $container->author_user = $value->getUsername();
            $container->time = $value->getTime();
            $container->timelineable_id = $value->getTimelineableId();
            $container->timelineable_type = $value->getTimelineableType();
            $container->call_to_action_uuid = $value->getCallToActionUuid();
            $container->call_to_action_deadline = $value->getCallToActionDeadline();

            return;
        }

        if (!$value instanceof TimelineDto) {
            return;
        }

        $container->uuid = $value->getUuid();
        $container->note = $value->getNote();
        $container->title = $value->getTitle();
        $container->time = $value->getTime();
        $container->timelineable_id = $value->getTimelineableId();
        $container->timelineable_type = $value->getTimelineableType();
    }
}
