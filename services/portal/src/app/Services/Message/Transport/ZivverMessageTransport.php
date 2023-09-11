<?php

declare(strict_types=1);

namespace App\Services\Message\Transport;

use App\Mail\Message;
use App\Models\Eloquent\EloquentMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

use function sprintf;

class ZivverMessageTransport implements MessageTransport
{
    public function __construct(
        private readonly string $mailer,
    ) {
    }

    public function send(EloquentMessage $eloquentMessage): ?string
    {
        $symfonyMessageCallable = static function (Email $message) use ($eloquentMessage): void {
            $headers = $message->getHeaders();

            if ($eloquentMessage->is_secure && $eloquentMessage->telephone !== null) {
                $zivverAccessRight = sprintf('%s sms %s', $eloquentMessage->to_email, $eloquentMessage->telephone);
                $headers->addTextHeader('zivver-access-right', $zivverAccessRight);
            }

            if ($eloquentMessage->expires_at === null) {
                return;
            }

            $daysDifference = CarbonImmutable::now()
                ->floorMinute()
                ->diffInDays($eloquentMessage->expires_at->floorMinute());
            $headers->addTextHeader('zivver-message-expiration', sprintf('delete P%sD', $daysDifference));
        };

        Mail::mailer($this->mailer)
            ->send(new Message($eloquentMessage, $symfonyMessageCallable));

        return null;
    }
}
