<?php

declare(strict_types=1);

namespace App\Models\Fields;

use App\Schema\Purpose\PurposeSpecification;

interface ProvidesPurposeSpecificationArray
{
    /**
     * @return array<string, array<string,PurposeSpecification>>
     */
    public function getContent(): array;
}
