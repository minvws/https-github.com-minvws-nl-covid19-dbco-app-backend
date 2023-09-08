<?php

declare(strict_types=1);

namespace App\Dto;

class MessageTemplateDto
{
    public function __construct(
        private readonly string $subject,
        private readonly string $text,
        private readonly bool $isSecure,
        private readonly string $mailLanguage,
        private readonly array $attachments,
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isSecure(): bool
    {
        return $this->isSecure;
    }

    public function getMailLanguage(): string
    {
        return $this->mailLanguage;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
