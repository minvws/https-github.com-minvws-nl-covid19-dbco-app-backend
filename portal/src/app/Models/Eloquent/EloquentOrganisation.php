<?php

namespace App\Models\Eloquent;

class EloquentOrganisation extends EloquentBaseModel
{
    protected $table = 'organisation';

    public function users()
    {
        return $this->belongsToMany('App\Models\Eloquent\EloquentUser', 'user_organisation')->withTimestamps();
    }
}
