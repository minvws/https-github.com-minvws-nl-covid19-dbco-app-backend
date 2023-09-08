<?php

declare(strict_types=1);

namespace App\Services\Message\Transport;

use App\Exceptions\AttachmentException;
use App\Exceptions\BadDataStateException;
use App\Exceptions\IdentifiedBsnNotValidAnymoreException;
use App\Exceptions\MessageException;
use App\Models\Eloquent\Attachment;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentTask;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\Dto\PseudoBsnLookup;
use App\Services\Bsn\BsnService;
use App\Services\Bsn\PseudoBsnLookupInputValidator;
use App\Services\Message\AttachmentService;
use App\Services\SecureMail\SecureMailClient;
use App\Services\SecureMail\SecureMailException;
use App\Services\SecureMail\SecureMailMessage;
use Webmozart\Assert\Assert;

use function sprintf;

class SecureMailMessageTransport implements MessageTransport
{
    public function __construct(
        private readonly AttachmentService $attachmentService,
        private readonly BsnService $bsnService,
        private readonly SecureMailClient $secureMailClient,
    ) {
    }

    /**
     * @throws MessageException
     */
    public function send(EloquentMessage $eloquentMessage): ?string
    {
        try {
            $isMessageValidForPseudoBsnLookup = $this->isMessageValidForPseudoBsnLookup($eloquentMessage);
        } catch (BadDataStateException $badDataStateException) {
            throw MessageException::fromThrowable($badDataStateException);
        }

        if ($eloquentMessage->pseudo_bsn !== null && $isMessageValidForPseudoBsnLookup) {
            $pseudoBsnLookup = $this->createPseudoBsnLookup($eloquentMessage);

            try {
                $pseudoBsnToken = $this->bsnService->newExchangeTokenForIdentifiedPseudoBsn(
                    $pseudoBsnLookup,
                    $eloquentMessage->pseudo_bsn,
                    $eloquentMessage->case->organisation->external_id,
                );
            } catch (IdentifiedBsnNotValidAnymoreException $exception) {
                throw MessageException::fromThrowable($exception);
            } catch (BsnException $exception) {
                throw new MessageException(
                    sprintf('PseudoBsn given but no token returned: %s', $exception->getMessage()),
                    $exception->getCode(),
                    $exception,
                );
            }
        }

        $aliasId = $eloquentMessage->task_uuid ?? $eloquentMessage->case_uuid;

        try {
            $attachments = $this->getAttachmentsForSecureMessage($eloquentMessage);
        } catch (AttachmentException $attachmentException) {
            throw MessageException::fromThrowable($attachmentException);
        }

        $secureMailMessage = SecureMailMessage::new(
            $aliasId,
            $eloquentMessage->from_name,
            $eloquentMessage->from_email,
            $eloquentMessage->to_name,
            $eloquentMessage->to_email,
            $eloquentMessage->telephone,
            $eloquentMessage->subject,
            $eloquentMessage->text,
            $eloquentMessage->from_name,
            $eloquentMessage->is_secure,
            $eloquentMessage->expires_at,
            $eloquentMessage->identity_required,
            $pseudoBsnToken ?? null,
            $attachments,
        );

        try {
            return $this->secureMailClient->postMessage($secureMailMessage);
        } catch (SecureMailException $secureMailException) {
            throw new MessageException($secureMailException->getMessage());
        }
    }

    private function createPseudoBsnLookup(EloquentMessage $eloquentMessage): PseudoBsnLookup
    {
        if ($eloquentMessage->task_uuid === null) {
            $dateOfBirth = $eloquentMessage->case->index->dateOfBirth;
            $address = $eloquentMessage->case->index->address;
        } else {
            $dateOfBirth = $eloquentMessage->task?->personal_details->dateOfBirth;
            $address = $eloquentMessage->task?->personal_details->address;
        }

        Assert::notNull($dateOfBirth);
        Assert::notNull($address);
        Assert::notNull($address->postalCode);
        Assert::notNull($address->houseNumber);

        return new PseudoBsnLookup($dateOfBirth, $address->postalCode, $address->houseNumber, $address->houseNumberSuffix);
    }

    /**
     * @throws BadDataStateException
     */
    private function isMessageValidForPseudoBsnLookup(EloquentMessage $eloquentMessage): bool
    {
        if ($eloquentMessage->task_uuid === null) {
            return $this->isCaseValidForPseudoBsnLookup($eloquentMessage->case, $eloquentMessage->identity_required);
        }

        return $this->isTaskValidForPseudoBsnLookup($eloquentMessage->task, $eloquentMessage->identity_required);
    }

    /**
     * @throws BadDataStateException
     */
    private function isCaseValidForPseudoBsnLookup(?EloquentCase $case, bool $identityRequired): bool
    {
        if (
            $case !== null
            && PseudoBsnLookupInputValidator::isValid($case->index->dateOfBirth, $case->index->address)
        ) {
            return true;
        }

        if ($identityRequired) {
            throw new BadDataStateException('Missing data when sending identity required message.');
        }

        return false;
    }

    /**
     * @throws BadDataStateException
     */
    private function isTaskValidForPseudoBsnLookup(?EloquentTask $task, bool $identityRequired): bool
    {
        if (
            $task !== null
            && PseudoBsnLookupInputValidator::isValid($task->personal_details->dateOfBirth, $task->personal_details->address)
        ) {
            return true;
        }

        if ($identityRequired) {
            throw new BadDataStateException('Missing data when sending identity required message.');
        }

        return false;
    }

    /**
     * @return array<array{filename: string, content: string, mime_type: string}>
     *
     * @throws AttachmentException
     */
    private function getAttachmentsForSecureMessage(EloquentMessage $eloquentMessage): array
    {
        $attachments = [];

        /** @var Attachment $attachment */
        foreach ($eloquentMessage->attachments as $attachment) {
            $attachments[] = $this->attachmentService->convertToArray($attachment);
        }

        return $attachments;
    }
}
