<?php

declare(strict_types=1);

namespace App\Repositories\Dossier;

use App\Models\Dossier\Contact;
use App\Models\Dossier\Dossier;
use Illuminate\Support\Facades\DB;

class ContactRepository
{
    public function getContact(int $id): ?Contact
    {
        return Contact::query()->find($id);
    }

    public function makeContact(Dossier $dossier): Contact
    {
        $contact = new Contact();
        $contact->dossier()->associate($dossier);
        return $contact;
    }

    public function saveContact(Contact $contact): void
    {
        DB::transaction(static function () use ($contact): void {
            $contact->save();
        });
    }

    public function deleteContact(Contact $contact): void
    {
        DB::transaction(static function () use ($contact): void {
            $contact->delete();
        });
    }
}
