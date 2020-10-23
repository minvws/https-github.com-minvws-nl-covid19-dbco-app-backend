<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use App\Models\User;

class CovidCase extends BaseModel
{
    protected $table = "case";

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($case) {
            $user = Session::get('user');
            $case->owner = $user->id;
        });
    }

}
