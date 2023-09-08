<?php

declare(strict_types=1);

namespace App\Schema\Update;

use App\Schema\Fields\Field;

class UpdateFieldDiff
{
    private Field $field;

    /** @var mixed|null */
    private $oldValue;

    /** @var mixed|null */
    private $newValue;

    public function __construct(Field $field, mixed $oldValue, mixed $newValue)
    {
        $this->field = $field;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    public function getNewValue(): mixed
    {
        return $this->newValue;
    }
}
