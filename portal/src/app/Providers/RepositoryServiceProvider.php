<?php

namespace App\Providers;

use App\Repositories\ApiPairingRepository;
use App\Repositories\CaseRepository;
use App\Repositories\DbCaseRepository;
use App\Repositories\PairingRepository;
use App\Repositories\TaskRepository;
use App\Repositories\DbTaskRepository;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;

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
        $this->app->bind(PairingRepository::class, ApiPairingRepository::class);

        $this->app->when(ApiPairingRepository::class)
                  ->needs(GuzzleClient::class)
                  ->give(fn () => new GuzzleClient(config('services.private_api.client_options')));

        $this->app->when(ApiPairingRepository::class)
            ->needs('$jwtSecret')
            ->give(fn () => config('services.private_api.jwt_secret'));

    }

}
