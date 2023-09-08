<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function is_string;
use function trans;

class IsDeceasedCareProfessionalRule implements ImplicitRule
{
    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return YesNoUnknown::from($value) !== YesNoUnknown::yes();
    }

    public function message(): string
    {
        $message = 'This person was a Care Professional.';
        $result = trans('validation.' . $message);
        return is_string($result) ? $result : $message;
    }
}
