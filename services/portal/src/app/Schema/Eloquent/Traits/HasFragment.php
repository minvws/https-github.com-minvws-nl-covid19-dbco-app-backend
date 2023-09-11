<?php

declare(strict_types=1);

namespace App\Schema\Eloquent\Traits;

use App\Schema\Eloquent\Relations\HasOneFragment;
use App\Schema\FragmentModel;
use App\Schema\SchemaObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns;
use Illuminate\Database\Eloquent\Model;

use function lcfirst;

/**
 * Adds a special hasOneFragment relation (and maybe a hasManyFragments in the future).
 *
 * The HasOneFragment relation combined with this trait allows the one-to-one relation to behave more like a cast /
 * a column that can be assigned to / read from. A lot of the existing code base relies on this behavior, and
 * especially the existing unit tests. Concretely this relationship combined with this traits add the following
 * functionality:
 *
 * - Relation model is automatically saved after the owner is saved.
 * - Relation field can be directly assigned to (automatic association).
 *
 * @uses Concerns\HasAttributes
 * @uses Concerns\HasEvents
 * @uses Concerns\HasRelationships
 */
trait HasFragment
{
    /** @var array<string, array<FragmentModel>> */
    private array $replacedFragments = [];

    private bool $isSavingFragments = false;

    protected static function bootHasFragment(): void
    {
        static::saved(static fn (self $model) => $model->saveFragments());
    }

    private function isOwnerOfFragment(HasOneFragment $relation, FragmentModel $fragment): bool
    {
        $owner = $fragment->{$relation->getForeignRelationName()};
        return $owner instanceof self && $owner->getKey() === $this->getKey();
    }

    /**
     * @param array<FragmentModel> $fragments
     */
    private function deleteReplacedFragmentsForRelation(HasOneFragment $relation, array $fragments): void
    {
        foreach ($fragments as $fragment) {
            if ($this->isOwnerOfFragment($relation, $fragment)) {
                $fragment->delete();
            }
        }
    }

    private function deleteReplacedFragments(): void
    {
        foreach ($this->replacedFragments as $relationName => $fragments) {
            $relation = $this->$relationName();
            if (!$relation instanceof HasOneFragment) {
                continue;
            }

            $this->deleteReplacedFragmentsForRelation($relation, $fragments);
        }

        $this->replacedFragments = [];
    }

    private function saveLoadedFragments(): void
    {
        foreach ($this->getRelations() as $name => $fragment) {
            if (!$fragment instanceof FragmentModel) {
                continue;
            }

            $relation = $this->$name();
            if (!$relation instanceof HasOneFragment) {
                continue;
            }

            if ($this->isOwnerOfFragment($relation, $fragment)) {
                $relation->save($fragment);
            }
        }
    }

    protected function saveFragments(): void
    {
        if ($this->isSavingFragments) {
            return;
        }

        $this->isSavingFragments = true;

        try {
            $this->deleteReplacedFragments();
            $this->saveLoadedFragments();
        } finally {
            $this->isSavingFragments = false;
        }
    }

    /**
     * Add fragment relation.
     *
     * @param class-string<FragmentModel> $class
     * @param string|null $foreignRelationName Name of the belongsTo relationship referencing back to this model.
     * @param string|null $localRelationName Name of this relationship.
     */
    public function hasOneFragment(string $class, ?string $foreignKey = null, ?string $localKey = null, ?string $foreignRelationName = null, ?string $localRelationName = null): HasOneFragment
    {
        /** @var Model $instance */
        $instance = $this->newRelatedInstance($class);

        if ($foreignRelationName === null) {
            $foreignRelationName = lcfirst(static::getSchema()->getName());
        }

        if ($localRelationName === null) {
            $localRelationName = lcfirst($class::getSchema()->getName());
        }

        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOneFragment(
            $instance->newQuery(),
            $this,
            $instance->getTable() . '.' . $foreignKey,
            $localKey,
            $foreignRelationName,
            $localRelationName,
        );
    }

    protected function newHasOneFragment(Builder $query, Model $parent, string $foreignKey, string $localKey, string $foreignRelationName, string $localRelationName): HasOneFragment
    {
        return
            (new HasOneFragment($query, $parent, $foreignKey, $localKey, $foreignRelationName, $localRelationName))
            ->withDefault(static function (SchemaObject $child, SchemaObject $parent) use ($foreignRelationName, $localRelationName) {
                    $model = $parent->getSchemaVersion()->getExpectedField($localRelationName)->newInstance();
                    $model->$foreignRelationName()->associate($parent);
                    return $model;
            });
    }

    /**
     * @inheritDoc
     */
    public function hasSetMutator($key)
    {
        if ($this->isRelation($key) && $this->$key() instanceof HasOneFragment) {
            return true;
        }

        return parent::hasSetMutator($key);
    }

    /**
     * @inheritDoc
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if (!$this->isRelation($key)) {
            return parent::setMutatedAttributeValue($key, $value);
        }

        $relation = $this->$key();
        if (!$relation instanceof HasOneFragment) {
            return parent::setMutatedAttributeValue($key, $value);
        }

        $current = $this->$key;
        if (
            $current instanceof FragmentModel
            && $current->exists
            &&
            (
                !$value instanceof FragmentModel
                || $current->getKey() !== $value->getKey()
            )
        ) {
            $this->replacedFragments[$key][] = $current;
        }

        if ($value instanceof FragmentModel) {
            $foreignRelationName = $relation->getForeignRelationName();

            $currentOwner = $value->$foreignRelationName;
            if ($currentOwner !== $this && $currentOwner instanceof self) {
                $currentOwner->unsetRelation($relation->getLocalRelationName());
            }

            $value->$foreignRelationName()->associate($this);
        }

        $this->setRelation($key, $value);

        return null;
    }
}
