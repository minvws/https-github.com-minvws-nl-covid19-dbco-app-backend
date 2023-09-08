<?php

declare(strict_types=1);

namespace App\Services\Assignment\Enum;

use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Note;

enum AssignmentModelEnum: int
{
    case Case_ = 1; // https://github.com/pdepend/pdepend/issues/640
    case Note = 2;
    case CallToAction = 3;

    public function getClass(): string
    {
        return match ($this->name) {
            self::Case_->name => EloquentCase::class,
            self::Note->name => Note::class,
            self::CallToAction->name => CallToAction::class,
        };
    }
}
