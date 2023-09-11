<?php

declare(strict_types=1);

namespace App\Dto;

class CallToActionTimelineDto extends TimelineDto
{
    private ?string $username;
    private ?string $callToActionUuid;
    private ?string $callToActionDeadline;

    public function __construct(
        string $uuid,
        string $title,
        ?string $note,
        string $time,
        string $timelineableId,
        string $timelineableType,
        ?string $username,
        ?string $callToActionUuid,
        ?string $callToActionDeadline,
    ) {
        parent::__construct($uuid, $title, $note, $time, $timelineableId, $timelineableType);

        $this->username = $username;
        $this->callToActionUuid = $callToActionUuid;
        $this->callToActionDeadline = $callToActionDeadline;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getCallToActionUuid(): ?string
    {
        return $this->callToActionUuid;
    }

    public function getCallToActionDeadline(): ?string
    {
        return $this->callToActionDeadline;
    }
}
