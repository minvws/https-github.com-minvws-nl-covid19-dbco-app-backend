<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Traits;

use MinVWS\DBCO\Enum\Models\ContextCategory;

trait ContextCategoryGroupCategories
{
    /**
     * Returns the categories for this group.
     *
     * @return ContextCategory[]
     */
    protected function getCategories(): array
    {
        return array_filter(ContextCategory::all(), fn (ContextCategory $c) => $c->group === $this);
    }
}
