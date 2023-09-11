<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use DateTimeInterface;

final class SecureMailMessage
{
    public const TYPE_DIRECT = 'direct';
    public const TYPE_SECURE = 'secure';

    public ?string $id = null;
    public ?string $aliasId;
    public string $fromName;
    public string $fromEmail;
    public string $toName;
    public string $toEmail;
    public ?string $phoneNumber;
    public string $subject;
    public string $text;
    public string $footer;
    public string $type = self::TYPE_SECURE;
    public ?DateTimeInterface $expiresAt;
    public bool $identityRequired;
    public ?string $pseudoBsnToken;

    /** @var array<array{filename: string, content: string, mime_type: string}> $attachments */
    public array $attachments;

    /**
     * @param array<array{filename: string, content: string, mime_type: string}> $attachments
     */
    public static function new(
        ?string $aliasId,
        string $fromName,
        string $fromEmail,
        string $toName,
        string $toEmail,
        ?string $phoneNumber,
        string $subject,
        string $text,
        string $footer,
        bool $secure,
        ?DateTimeInterface $expiresAt,
        bool $identityRequired,
        ?string $pseudoBsnToken,
        array $attachments = [],
    ): self {
        $self = new self();
        $self->aliasId = $aliasId;
        $self->fromName = $fromName;
        $self->fromEmail = $fromEmail;
        $self->toName = $toName;
        $self->toEmail = $toEmail;
        $self->phoneNumber = $phoneNumber;
        $self->subject = $subject;
        $self->text = $text;
        $self->footer = $footer;
        $self->type = $secure ? self::TYPE_SECURE : self::TYPE_DIRECT;
        $self->expiresAt = $expiresAt;
        $self->identityRequired = $identityRequired;
        $self->pseudoBsnToken = $pseudoBsnToken;
        $self->attachments = $attachments;

        return $self;
    }

    public function toArray(): array
    {
        return [
            'aliasId' => $this->aliasId,
            'fromName' => $this->fromName,
            'fromEmail' => $this->fromEmail,
            'toName' => $this->toName,
            'toEmail' => $this->toEmail,
            'phoneNumber' => $this->phoneNumber,
            'subject' => $this->subject,
            'text' => $this->text,
            'footer' => $this->footer,
            'type' => $this->type,
            'expiresAt' => $this->expiresAt !== null ? $this->expiresAt->format('c') : null,
            'identityRequired' => $this->identityRequired,
            'pseudoBsnToken' => $this->pseudoBsnToken,
            'attachments' => $this->attachments,
        ];
    }
}
