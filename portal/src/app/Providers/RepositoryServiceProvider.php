<?php

namespace App\Providers;

use App\Repositories\AnswerRepository;
use App\Repositories\ApiCaseExportRepository;
use App\Repositories\ApiPairingRepository;
use App\Repositories\CaseExportRepository;
use App\Repositories\CaseRepository;
use App\Repositories\DbAnswerRepository;
use App\Repositories\DbCaseRepository;
use App\Repositories\DbOrganisationRepository;
use App\Repositories\DbQuestionnaireRepository;
use App\Repositories\DbQuestionRepository;
use App\Repositories\DbUserRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\PairingRepository;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\TaskRepository;
use App\Repositories\DbTaskRepository;
use App\Repositories\UserRepository;
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
        $this->app->bind(CaseExportRepository::class, ApiCaseExportRepository::class);
        $this->app->bind(TaskRepository::class, DbTaskRepository::class);
        $this->app->bind(PairingRepository::class, ApiPairingRepository::class);
        $this->app->bind(QuestionnaireRepository::class, DbQuestionnaireRepository::class);
        $this->app->bind(AnswerRepository::class, DbAnswerRepository::class);
        $this->app->bind(QuestionRepository::class, DbQuestionRepository::class);
        $this->app->bind(UserRepository::class, DbUserRepository::class);
        $this->app->bind(OrganisationRepository::class, DbOrganisationRepository::class);

        $this->app->when(ApiPairingRepository::class)
                  ->needs(GuzzleClient::class)
                  ->give(fn () => new GuzzleClient(config('services.private_api.client_options')));

        $this->app->when(ApiPairingRepository::class)
            ->needs('$jwtSecret')
            ->give(fn () => config('services.private_api.jwt_secret'));

        $this->app->when(ApiCaseExportRepository::class)
            ->needs(GuzzleClient::class)
            ->give(fn () => new GuzzleClient(config('services.healthauthority_api.client_options')));
    }
}
