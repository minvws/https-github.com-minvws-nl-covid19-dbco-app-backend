<?php

declare(strict_types=1);

namespace App\Services\Policy;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

final class ContactPolicyFacts
{
    public ?CarbonImmutable $dateOfSymptomOnset = null;
    public ?CarbonImmutable $dateOfTest = null;

    private function __construct(
        public readonly ?ContactCategory $contactCategory,
        public readonly ?YesNoUnknown $immunity,
        public readonly ?yesNoUnknown $closeContactDuringQuarantine,
    ) {
    }

    public static function create(
        ContactCategory $contactCategory,
        ?YesNoUnknown $immunity,
        ?yesNoUnknown $closeContactDuringQuarantine,
    ): self {
        return new self(
            $contactCategory,
            self::handleImmunity($immunity),
            self::handleCloseContactDuringQuarantine($closeContactDuringQuarantine),
        );
    }

    private static function handleImmunity(?YesNoUnknown $immunity): YesNoUnknown
    {
        if ($immunity === null) {
            return YesNoUnknown::unknown();
        }

        return $immunity;
    }

    private static function handleCloseContactDuringQuarantine(?YesNoUnknown $closeContactDuringQuarantine): YesNoUnknown
    {
        if ($closeContactDuringQuarantine === null) {
            return YesNoUnknown::unknown();
        }

        return $closeContactDuringQuarantine;
    }

    public function withDateOfSymptomOnset(DateTimeInterface $dateOfSymptomOnset): ContactPolicyFacts
    {
        $facts = clone $this;
        $facts->dateOfSymptomOnset = CarbonImmutable::instance($dateOfSymptomOnset);
        return $facts;
    }
}
