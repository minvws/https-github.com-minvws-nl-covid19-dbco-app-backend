<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Traits;

use MinVWS\DBCO\Enum\Models\ContextCategoryGroup;

trait ContextListViewGroups
{
    /**
     * Returns the category groups for this view.
     *
     * @return ContextCategoryGroup[]
     */
    protected function getGroups(): array
    {
        return array_filter(ContextCategoryGroup::all(), fn (ContextCategoryGroup $c) => $c->view === $this);
    }
}
