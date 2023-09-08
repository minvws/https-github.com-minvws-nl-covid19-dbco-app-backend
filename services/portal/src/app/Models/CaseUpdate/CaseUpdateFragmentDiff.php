<?php

declare(strict_types=1);

namespace App\Models\CaseUpdate;

use App\Schema\Update\UpdateDiff;

class CaseUpdateFragmentDiff
{
    private string $key;
    private string $fragmentName;
    private UpdateDiff $diff;

    public function __construct(string $key, string $fragmentName, UpdateDiff $diff)
    {
        $this->key = $key;
        $this->fragmentName = $fragmentName;
        $this->diff = $diff;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getFragmentName(): string
    {
        return $this->fragmentName;
    }

    public function getDiff(): UpdateDiff
    {
        return $this->diff;
    }
}
