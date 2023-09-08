<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

use function array_values;

class PurposeSpecification
{
    /** @var array<PurposeDetail> */
    private array $purposes = [];

    /**
     * @param array<PurposeDetail> $purposeDetails
     */
    public function __construct(
        array $purposeDetails,
        public readonly ?string $remark = null,
    ) {
        foreach ($purposeDetails as $detail) {
            $this->purposes[$detail->purpose->getIdentifier()] = $detail;
        }
    }

    /**
     * @return array<PurposeDetail>
     */
    public function getAllPurposeDetails(): array
    {
        return array_values($this->purposes);
    }

    public function getPurposeDetail(Purpose $purpose): ?PurposeDetail
    {
        return $this->purposes[$purpose->getIdentifier()] ?? null;
    }

    public function hasPurpose(Purpose $purpose): bool
    {
        return isset($this->purposes[$purpose->getIdentifier()]);
    }
}
