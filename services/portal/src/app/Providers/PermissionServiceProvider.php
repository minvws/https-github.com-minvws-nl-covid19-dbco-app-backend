<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Eloquent\EloquentUser;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        /** @var Gate $gate */
        $gate = $this->app->make(Gate::class);

        $gate->before(static function (Authorizable $user, string $ability): ?bool {
            if ($user instanceof EloquentUser) {
                return $user->hasPermission($ability) ?: null;
            }

                return null;
        });
    }
}
