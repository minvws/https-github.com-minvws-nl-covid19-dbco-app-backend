<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Note;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\Enum;

class NoteRepository
{
    public function createNote(EloquentCase $case, Enum $type, string $note, EloquentUser $user): ?Note
    {
        $organisation = $user->getOrganisation();

        $note = new Note([
            'case_created_at' => $case->createdAt,
            'note' => $note,
            'user_name' => $user->name,
            'organisation_name' => $organisation->name ?? null,
            'type' => $type,
        ]);

        $note->user()->associate($user);

        return $case->notes()->save($note) !== false ? $note : null;
    }

    public function getNotes(EloquentCase $case): Collection
    {
        return $case->notes()->orderByDesc('created_at')->get();
    }
}
