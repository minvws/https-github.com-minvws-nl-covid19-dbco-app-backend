<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use Illuminate\Database\Eloquent\Model;

use function is_null;

trait SafeIssetFragment
{
    /**
     * Checking for the fragment's existance causes an side-effect. It will create the fragment whenever it doesn't
     * exist, meaning that isset() will always return true for keys that could hold a fragment. Using
     * ->getAttributes() does not seem to have that effect. The side-effect only happens for fragments that extends
     * the \App\Schema\FragmentCompat class.
     */
    protected static function issetFragment(Model $model, string $fragmentRelationName): bool
    {
        return !is_null($model->getAttributes()[$fragmentRelationName] ?? null);
    }
}
