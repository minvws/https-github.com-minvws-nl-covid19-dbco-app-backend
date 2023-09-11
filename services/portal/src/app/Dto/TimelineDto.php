<?php

declare(strict_types=1);

namespace App\Dto;

class TimelineDto
{
    protected string $uuid;
    protected string $title;
    protected ?string $note;
    protected string $time;
    protected string $timelineableId;
    protected string $timelineableType;

    public function __construct(
        string $uuid,
        string $title,
        ?string $note,
        string $time,
        string $timelineableId,
        string $timelineableType,
    ) {
        $this->title = $title;
        $this->note = $note;
        $this->time = $time;
        $this->uuid = $uuid;
        $this->timelineableId = $timelineableId;
        $this->timelineableType = $timelineableType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTimelineableId(): string
    {
        return $this->timelineableId;
    }

    public function getTimelineableType(): string
    {
        return $this->timelineableType;
    }
}
