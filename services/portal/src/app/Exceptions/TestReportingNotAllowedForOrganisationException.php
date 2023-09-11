<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Eloquent\EloquentOrganisation;

use function sprintf;

final class TestReportingNotAllowedForOrganisationException extends SkipTestResultImportException
{
    public function __construct(EloquentOrganisation $organisation)
    {
        parent::__construct(
            sprintf(
                'Test reporting not allowed for organisation with uuid "%s"',
                $organisation->uuid,
            ),
        );
    }
}
