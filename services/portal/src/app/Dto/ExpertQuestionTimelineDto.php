<?php

declare(strict_types=1);

namespace App\Dto;

class ExpertQuestionTimelineDto extends TimelineDto
{
    private string $questionUserName;
    private ?string $answer;
    private ?string $answerUserName;
    private ?string $answerTime;

    public function __construct(
        string $uuid,
        string $title,
        ?string $note,
        string $time,
        string $timelineableId,
        string $timelineableType,
        string $questionUserName,
        ?string $answer,
        ?string $answerUserName,
        ?string $answerTime,
    ) {
        parent::__construct($uuid, $title, $note, $time, $timelineableId, $timelineableType);

        $this->questionUserName = $questionUserName;
        $this->answer = $answer;
        $this->answerUserName = $answerUserName;
        $this->answerTime = $answerTime;
    }

    public function getQuestionUserName(): string
    {
        return $this->questionUserName;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function getAnswerUser(): ?string
    {
        return $this->answerUserName;
    }

    public function getAnswerTime(): ?string
    {
        return $this->answerTime;
    }
}
