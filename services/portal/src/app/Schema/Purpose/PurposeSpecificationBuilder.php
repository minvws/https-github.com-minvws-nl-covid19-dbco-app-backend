<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

class PurposeSpecificationBuilder
{
    /** @var array<PurposeDetail> */
    private array $purposes = [];
    private ?string $remark = null;

    /**
     * @template TSubPurpose of SubPurpose
     *
     * @param Purpose<TSubPurpose> $purpose
     * @param TSubPurpose $subPurpose
     *
     * @return $this
     */
    public function addPurpose(Purpose $purpose, SubPurpose $subPurpose): self
    {
        $this->purposes[] = new PurposeDetail($purpose, $subPurpose);
        return $this;
    }

    /**
     * @template TSubPurpose of SubPurpose
     *
     * @param array<Purpose<TSubPurpose>> $purposes
     * @param TSubPurpose $subPurpose
     *
     * @return $this
     */
    public function addPurposes(array $purposes, SubPurpose $subPurpose): self
    {
        foreach ($purposes as $purpose) {
            $this->addPurpose($purpose, $subPurpose);
        }

        return $this;
    }

    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }

    public function copyFromPurposeSpecification(PurposeSpecification $purposeSpecification): self
    {
        $this->purposes = $purposeSpecification->getAllPurposeDetails();
        $this->remark = $purposeSpecification->remark;
        return $this;
    }

    public function build(): PurposeSpecification
    {
        return new PurposeSpecification($this->purposes, $this->remark);
    }
}
