<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Contracts;

interface Validatable
{
    public const SEVERITY_LEVEL_FATAL = 'fatal';
    public const SEVERITY_LEVEL_WARNING = 'warning';
    public const SEVERITY_LEVEL_NOTICE = 'notice';
    public const SEVERITY_LEVEL_OSIRIS_APPROVED = 'osiris_approved';
    public const SEVERITY_LEVEL_OSIRIS_FINISHED = 'osiris_finished';

    /**
     * Returns a list of validation rules to validate the model.
     *
     * Validation rules should be compatible with Laravel's validator.
     *
     * The validation rules should be indexed by the severity level:
     * - fatal: for fatal errors, data should not be saved and an error should be returned
     * - warning: data can be saved, however the errors should be fixed before making the case final
     * - notice: things to alert the user on, but are not necessarily wrong
     *
     * @param array $data Data to be validated. Can be used to conditionally add certain rules.
     *
     * @return array
     */
    public static function validationRules(array $data): array;
}
