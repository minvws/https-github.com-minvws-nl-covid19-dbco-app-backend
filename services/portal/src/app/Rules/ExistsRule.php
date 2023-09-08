<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Eloquent\EloquentBaseModel;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\QueryException;

use function class_exists;
use function collect;

class ExistsRule implements Rule
{
    private string $model;
    private string $field;

    public function __construct(string $model, string $field)
    {
        $this->model = $model;
        $this->field = $field;
    }

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        // make sure we have a model
        if (!class_exists($this->model)) {
            return false;
        }
        $modelInstance = new $this->model();
        if (!$modelInstance instanceof EloquentBaseModel) {
            return false;
        }

        // make sure value is an array of values
        $values = collect($value);

        // retrieve values
        try {
            $databaseValues = $this->model::query()
                ->whereIn($this->field, $values->values())
                ->pluck($this->field);
        } catch (QueryException $queryException) {
            // if query fails (e.g. given column does not exist), act is if no results found
            $databaseValues = collect();
        }

        // fail if there any items present in the given set, that are not found in the database
        return $values->diff($databaseValues->values())->count() <= 0;
    }

    public function message(): string
    {
        return 'one of the given values was not found in the database';
    }
}
