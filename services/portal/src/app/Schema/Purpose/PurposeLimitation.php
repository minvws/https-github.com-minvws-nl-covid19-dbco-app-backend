<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

use function in_array;

class PurposeLimitation
{
    /**
     * @param array<Purpose> $purposes
     */
    public function __construct(private readonly array $purposes)
    {
    }

    /**
     * @return array<Purpose>
     */
    public function getPurposes(): array
    {
        return $this->purposes;
    }

    public function hasPurpose(Purpose $purpose): bool
    {
        return in_array($purpose, $this->getPurposes(), true);
    }

    /**
     * Should a field be included given its purpose specification and the currently configured purpose limitation?
     */
    public function isIncluded(PurposeSpecification $purposeSpecification): bool
    {
        foreach ($this->purposes as $purpose) {
            if ($purposeSpecification->hasPurpose($purpose)) {
                return true;
            }
        }

        return false;
    }
}
