<?php

namespace App\Providers;

use App\Repositories\CaseRepository;
use App\Repositories\DbCaseRepository;
use App\Repositories\TaskRepository;
use App\Repositories\DbTaskRepository;
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
        $this->app->bind(TaskRepository::class, DbTaskRepository::class);
    }

}
