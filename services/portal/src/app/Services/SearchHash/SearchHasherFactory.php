<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use App\Services\SearchHash\EloquentCase\Contact\ContactSearchHasher;
use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use App\Services\SearchHash\EloquentCase\Index\IndexSearchHasher;
use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use App\Services\SearchHash\EloquentTask\General\GeneralSearchHasher;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsSearchHasher;
use Illuminate\Contracts\Foundation\Application;

final class SearchHasherFactory
{
    public function __construct(
        private readonly Application $app,
    ) {
    }

    public function covidCaseContact(ContactHash $contactHash): ContactSearchHasher
    {
        return $this->app->make(ContactSearchHasher::class, ['valueObject' => $contactHash]);
    }

    public function covidCaseIndex(IndexHash $indexHash): IndexSearchHasher
    {
        return $this->app->make(IndexSearchHasher::class, ['valueObject' => $indexHash]);
    }

    public function taskGeneral(GeneralHash $generalHash): GeneralSearchHasher
    {
        return $this->app->make(GeneralSearchHasher::class, ['valueObject' => $generalHash]);
    }

    public function taskPersonalDetails(PersonalDetailsHash $personalDetailsHash): PersonalDetailsSearchHasher
    {
        return $this->app->make(PersonalDetailsSearchHasher::class, ['valueObject' => $personalDetailsHash]);
    }
}
