<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

use function assert;

class NCOVLastInf1ezktDtBuilder extends AbstractSingleValueBuilder
{
    /*
     * Wat is de eerste ziektedag (testdatum voor asymptomaten)
     * van de vorige episode met SARS-CoV-2 positieve testuitslag?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));

        return Utils::formatDate($case->test->previousInfectionDateOfSymptom);
    }
}
