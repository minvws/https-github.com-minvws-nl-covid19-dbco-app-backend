<?php

declare(strict_types=1);

namespace App\Services\Message\Transport;

use App\Exceptions\MessageException;
use App\Mail\Message;
use App\Models\Eloquent\EloquentMessage;
use Illuminate\Support\Facades\Mail;

class SmtpMailerMessageTransport implements MessageTransport
{
    public function __construct(
        private readonly string $mailer,
    ) {
    }

    /**
     * @throws MessageException
     */
    public function send(EloquentMessage $eloquentMessage): ?string
    {
        if ($eloquentMessage->is_secure) {
            throw new MessageException('secure message should not be sent through smtp');
        }

        Mail::mailer($this->mailer)
            ->send(new Message($eloquentMessage));

        return null;
    }
}
