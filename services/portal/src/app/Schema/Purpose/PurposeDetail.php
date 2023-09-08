<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

class PurposeDetail
{
    /**
     * @template TSubPurpose of SubPurpose
     *
     * @param Purpose<TSubPurpose> $purpose
     * @param TSubPurpose $subPurpose
     */
    public function __construct(public readonly Purpose $purpose, public readonly SubPurpose $subPurpose)
    {
    }
}
