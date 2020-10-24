<?php

namespace App\Providers;

use App\Repositories\CaseRepository;
use App\Repositories\DbCaseRepository;
use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CaseRepository::class, DbCaseRepository::class);
    }

}
