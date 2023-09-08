<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\CaseExport;

use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Models\Eloquent\EloquentCase;
use Throwable;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class CaseExportRejectedException extends BaseCaseExportException
{
    private function __construct(
        public readonly string $caseUuid,
        public readonly array $errors,
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromErrorResponse(EloquentCase $case, ErrorResponseException $previous): self
    {
        return new self(
            $case->uuid,
            $previous->errors,
            sprintf(
                'Case export was rejected by Osiris; %s',
                json_encode(
                    ['case' => $case->uuid, 'reason' => $previous->reason, 'errors' => $previous->errors],
                    flags: JSON_THROW_ON_ERROR,
                ),
            ),
            $previous->getCode(),
            $previous,
        );
    }
}
