<?php

declare(strict_types=1);

namespace App\Schema\Purpose\Traits;

use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Purpose\PurposeSpecificationException;
use App\Schema\Purpose\SubPurpose;
use Closure;

trait HasPurposeSpecification
{
    private ?PurposeSpecification $purposeSpecification = null;

    /**
     * @codeCoverageIgnore
     */
    protected function getFallbackPurposeSpecification(): ?PurposeSpecification
    {
        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getFallbackSubPurposeOverride(): ?SubPurpose
    {
        return null;
    }

    public function getPurposeSpecification(): PurposeSpecification
    {
        if ($this->purposeSpecification !== null) {
            return $this->purposeSpecification;
        }

        if ($this->getFallbackPurposeSpecification() !== null) {
            $builder = new PurposeSpecificationBuilder();
            foreach ($this->getFallbackPurposeSpecification()->getAllPurposeDetails() as $detail) {
                $subPurpose = $this->getFallbackSubPurposeOverride() ?? $detail->subPurpose;
                $builder->addPurpose($detail->purpose, $subPurpose);
            }

            $remark = $this->getFallbackPurposeSpecification()->remark;
            if ($remark !== null) {
                $builder->setRemark($remark);
            }

            return $builder->build();
        } else {
            $this->purposeSpecification = new PurposeSpecification([]);
        }

        return $this->purposeSpecification;
    }

    public function hasPurposeSpecification(): bool
    {
        return $this->purposeSpecification !== null;
    }

    public function specifyPurpose(Closure $closure): static
    {
        if ($this->hasPurposeSpecification()) {
            throw new PurposeSpecificationException("Purpose already specified");
        }

        $builder = new PurposeSpecificationBuilder();
        $closure($builder);
        $this->purposeSpecification = $builder->build();

        return $this;
    }
}
