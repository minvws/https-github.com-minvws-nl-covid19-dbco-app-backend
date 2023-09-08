<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CallToActionNote;
use App\Models\Eloquent\EloquentUser;
use Illuminate\Support\Collection;

interface CallToActionNoteRepository
{
    public function createCallToActionNote(
        string $note,
        CallToAction $callToAction,
        EloquentUser $user,
    ): CallToActionNote;

    public function listCallToActionNotes(CallToAction $callToAction, int $limit): Collection;
}
