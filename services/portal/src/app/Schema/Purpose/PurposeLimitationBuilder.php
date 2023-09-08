<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

use function array_values;

class PurposeLimitationBuilder
{
    /** @var array<Purpose> */
    private array $purposes = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return $this
     */
    public function addPurpose(Purpose $purpose): self
    {
        $this->purposes[$purpose->getIdentifier()] = $purpose;
        return $this;
    }

    /**
     * @param array<Purpose> $purposes
     *
     * @return $this
     */
    public function addPurposes(array $purposes): self
    {
        foreach ($purposes as $purpose) {
            $this->addPurpose($purpose);
        }

        return $this;
    }

    /**
     * Limit output to a single purpose.
     *
     * Existing purposes will be cleared.
     */
    public function setPurpose(Purpose $purpose): self
    {
        return $this->clearPurposes()->addPurpose($purpose);
    }

    public function clearPurposes(): self
    {
        $this->purposes = [];
        return $this;
    }

    public function build(): PurposeLimitation
    {
        return new PurposeLimitation(array_values($this->purposes));
    }
}
