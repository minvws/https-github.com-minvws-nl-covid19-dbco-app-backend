<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Exceptions\IntakeConfirmationException;
use App\Mail\IntakeConfirmation;
use App\Models\Eloquent\Intake;
use App\Services\Policy\IndexPolicyGuidelineProvider;
use Illuminate\Contracts\Mail\Mailer;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

class IntakeConfirmationService
{
    public function __construct(
        private readonly IndexPolicyGuidelineProvider $policyGuidelineProvider,
        private readonly LoggerInterface $logger,
        private readonly Mailer $mailer,
    ) {
    }

    /**
     * @throws IntakeConfirmationException
     */
    public function confirmToIndex(Intake $intake): void
    {
        if ($intake->contact === null) {
            return;
        }

        $this->logger->info(sprintf('Sending confirmation to index for intake "%s"', $intake->uuid));

        try {
            $this->mailer
                ->to($intake->contact->email)
                ->send(new IntakeConfirmation($intake, $this->policyGuidelineProvider));
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('Sending confirmation to index for intake "%s" failed', $intake->uuid));
            throw IntakeConfirmationException::fromThrowable($exception);
        }

        $this->logger->info(sprintf('Sending confirmation to index for intake "%s" success', $intake->uuid));
    }
}
