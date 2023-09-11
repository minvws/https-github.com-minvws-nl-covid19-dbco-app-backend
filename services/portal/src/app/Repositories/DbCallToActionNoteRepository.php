<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CallToActionNote;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DbCallToActionNoteRepository implements CallToActionNoteRepository
{
    public function createCallToActionNote(string $note, CallToAction $callToAction, EloquentUser $user): CallToActionNote
    {
        $note = new CallToActionNote([
            'created_at' => CarbonImmutable::now(),
            'note' => $note,
        ]);

        $note->callToAction()->associate($callToAction);
        $note->user()->associate($user);
        $note->save();

        return $note;
    }

    public function listCallToActionNotes(CallToAction $callToAction, int $limit = 0): Collection
    {
        $query = CallToActionNote::where('call_to_action_uuid', $callToAction->uuid)->orderBy('created_at', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
