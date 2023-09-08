<?php

declare(strict_types=1);

namespace App\Schema\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HasOneFragment extends HasOne
{
    private string $foreignRelationName;
    private string $localRelationName;

    /**
     * Create a new has one fragment relationship instance.
     */
    public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey, string $foreignRelationName, string $localRelationName)
    {
        parent::__construct($query, $parent, $foreignKey, $localKey);

        $this->foreignRelationName = $foreignRelationName;
        $this->localRelationName = $localRelationName;
    }

    public function getForeignRelationName(): string
    {
        return $this->foreignRelationName;
    }

    public function getLocalRelationName(): string
    {
        return $this->localRelationName;
    }
}
