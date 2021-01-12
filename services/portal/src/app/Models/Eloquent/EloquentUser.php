<?php

namespace App\Models\Eloquent;

use Illuminate\Contracts\Auth\Authenticatable;

class EloquentUser extends EloquentBaseModel implements Authenticatable
{
    protected $table = "bcouser";

    public function organisations()
    {
        return $this->belongsToMany(
                        'App\Models\Eloquent\EloquentOrganisation',
                            'user_organisation',
                    'user_uuid',
                    'organisation_uuid'
        )->withTimestamps();
    }

    public function getAuthIdentifierName()
    {
        return 'uuid';
    }

    public function getAuthIdentifier()
    {
        return $this->uuid;
    }

    public function getAuthPassword()
    {
        return 'not used since we only do auth via oauth';
    }

    public function getRememberToken()
    {
        return 'not used either';
    }

    public function setRememberToken($value)
    {
        // nothing to do. not implemented
    }

    public function getRememberTokenName()
    {
        return null;
    }
}
