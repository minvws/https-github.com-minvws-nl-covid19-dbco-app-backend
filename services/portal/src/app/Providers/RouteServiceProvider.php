<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Commands\PurgeSoftDeletedModels;
use App\Exceptions\CaseListNotFoundHttpException;
use App\Exceptions\CaseNotFoundHttpException;
use App\Exceptions\CaseQueueNotFoundHttpException;
use App\Exceptions\CaseUpdateNotFoundHttpException;
use App\Exceptions\ContextNotFoundHttpException;
use App\Exceptions\ExpertQuestionNotFoundHttpException;
use App\Exceptions\MessageNotFoundHttpException;
use App\Exceptions\PlaceNotFoundHttpException;
use App\Exceptions\TaskGroupNotFoundHttpException;
use App\Exceptions\TaskNotFoundHttpException;
use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Dossier\Contact;
use App\Models\Dossier\Dossier;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Place;
use App\Services\CaseListService;
use App\Services\CaseService;
use App\Services\CaseUpdate\CaseUpdateService;
use App\Services\Chores\CallToActionService;
use App\Services\ContextService;
use App\Services\Disease\DiseaseModelService;
use App\Services\Disease\DiseaseModelUIService;
use App\Services\Disease\DiseaseService;
use App\Services\Dossier\ContactService;
use App\Services\Dossier\DossierService;
use App\Services\ExpertQuestion\ExpertQuestionService;
use App\Services\MessageService;
use App\Services\PlaceService;
use App\Services\TaskService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RouteObject;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

use function app;
use function base_path;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     */
    public const HOME = '/';

    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->configureRouteBindings();

        $this->routes(function (): void {
            if ($this->config->get('app.type') === 'portal' || $this->app->runningUnitTests()) {
                Route::namespace($this->namespace)
                    ->group(base_path('routes/status.php'));

                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/api.php'));

                Route::prefix('api/admin')
                    ->name('api.admin.')
                    ->middleware('api')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/api-admin.php'));

                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/web.php'));
            }
        });
    }

    private function configureRouteBindings(): void
    {
        Route::bind('case', function ($value): EloquentCase {
            /** @var CaseService $caseService */
            $caseService = $this->app->make(CaseService::class);

            $case = $caseService->getCaseByUuid($value);
            if ($case === null) {
                throw new CaseNotFoundHttpException();
            }

            return $case;
        });
        Route::bind('softDeletedCase', function ($value): EloquentCase {
            /** @var CaseService $caseService */
            $caseService = $this->app->make(CaseService::class);

            $case = $caseService->getCaseIncludingSoftDeletes($value);
            if ($case === null || $this->isNotDeletedOrWithingPurgeWindow($case)) {
                throw new CaseNotFoundHttpException();
            }

            return $case;
        });
        Route::bind('caseUpdate', function ($value, RouteObject $route): CaseUpdate {
            /** @var CaseUpdateService $caseUpdateService */
            $caseUpdateService = $this->app->make(CaseUpdateService::class);

            $caseUpdate = $caseUpdateService->getCaseUpdateByUuid($value);
            if ($caseUpdate === null) {
                throw new CaseUpdateNotFoundHttpException();
            }

            /** @var EloquentCase $case */
            $case = $route->parameter('case');
            if ($case !== null && $caseUpdate->case->uuid !== $case->uuid) {
                throw new CaseUpdateNotFoundHttpException();
            }

            return $caseUpdate;
        });
        Route::bind('caseList', function ($value): CaseList {
            /** @var CaseListService $caseListService */
            $caseListService = $this->app->get(CaseListService::class);

            $caseList = $caseListService->getCaseListByUuid($value, false);
            if ($caseList === null) {
                throw new CaseListNotFoundHttpException();
            }

            return $caseList;
        });
        Route::bind('caseQueue', function ($value): CaseList {
            /** @var CaseListService $caseListService */
            $caseListService = $this->app->make(CaseListService::class);

            $caseList = $value === 'default'
                ? $caseListService->getDefaultCaseQueue(false)
                : $caseListService->getCaseListByUuid($value, false);

            if ($caseList === null || !$caseList->is_queue) {
                throw new CaseQueueNotFoundHttpException();
            }

            return $caseList;
        });
        Route::bind('context', function ($value): Context {
            /** @var ContextService $contextService */
            $contextService = $this->app->get(ContextService::class);

            $context = $contextService->getContext($value);
            if ($context === null) {
                throw new ContextNotFoundHttpException();
            }

            return $context;
        });
        Route::bind('task', function ($value): EloquentTask {
            /** @var TaskService $taskService */
            $taskService = $this->app->get(TaskService::class);

            $task = $taskService->getTaskByUuid($value);
            if ($task === null) {
                throw new TaskNotFoundHttpException();
            }

            return $task;
        });
        Route::bind('softDeletedTask', function ($value): EloquentTask {
            /** @var TaskService $taskService */
            $taskService = $this->app->get(TaskService::class);

            $task = $taskService->getTaskIncludingSoftDeletes($value);
            if ($task === null) {
                throw new TaskNotFoundHttpException();
            }

            return $task;
        });
        Route::bind('message', function ($value): EloquentMessage {
            /** @var MessageService $messageService */
            $messageService = $this->app->get(MessageService::class);

            $message = $messageService->getMessageByUuid($value);
            if ($message === null) {
                throw new MessageNotFoundHttpException();
            }

            return $message;
        });
        Route::bind('place', function ($value): Place {
            /** @var PlaceService $placeService */
            $placeService = $this->app->get(PlaceService::class);

            $place = $placeService->getPlace($value);
            if ($place === null) {
                throw new PlaceNotFoundHttpException();
            }

            return $place;
        });
        Route::bind('expertQuestion', function ($value): ExpertQuestion {
            /** @var ExpertQuestionService $expertQuestionService */
            $expertQuestionService = $this->app->get(ExpertQuestionService::class);

            $expertQuestion = $expertQuestionService->getExpertQuestionById($value);
            if ($expertQuestion === null) {
                throw new ExpertQuestionNotFoundHttpException();
            }

            return $expertQuestion;
        });
        Route::bind('callToAction', function ($value): CallToAction {
            /** @var CallToActionService $callToActionService */
            $callToActionService = $this->app->get(CallToActionService::class);

            return $callToActionService->getCallToAction($value);
        });
        Route::bind('messageTemplateType', static fn ($value): MessageTemplateType => MessageTemplateType::from($value));
        Route::bind('taskGroup', static function ($value): TaskGroup {
            try {
                return TaskGroup::from($value);
            } catch (Throwable $e) {
                throw new TaskGroupNotFoundHttpException($e);
            }
        });
        Route::bind('disease', static fn (string $id) => self::getDisease($id));
        Route::bind(
            'diseaseModel',
            static fn (string $id) => self::getDiseaseModel($id)
        );
        Route::bind(
            'diseaseModelVersion',
            static fn (string $version, RouteObject $route) => self::getDiseaseModelVersion($version, $route)
        );
        Route::bind(
            'diseaseModelUI',
            static fn (string $id) => self::getDiseaseModelUI($id)
        );

        Route::bind(
            'diseaseModelUIVersion',
            static fn (string $version, RouteObject $route) => self::getDiseaseModelUIVersion($version, $route)
        );
        Route::bind('dossier', static fn (string $id) => self::getDossier($id));
        Route::bind('dossierContact', static fn (string $id) => self::getDossierContact($id));
    }

    private static function getDossier(string $id): Dossier
    {
        $dossier = app(DossierService::class)->getDossier($id);
        if ($dossier === null) {
            throw new NotFoundHttpException();
        }
        return $dossier;
    }

    private static function getDossierContact(string $id): Contact
    {
        $contact = app(ContactService::class)->getContact($id);
        if ($contact === null) {
            throw new NotFoundHttpException();
        }
        return $contact;
    }

    private static function getDisease(string $id): Disease
    {
        $disease = app(DiseaseService::class)->getDisease($id);
        if ($disease === null) {
            throw new NotFoundHttpException();
        }
        return $disease;
    }

    private static function getDiseaseModel(string $id): DiseaseModel
    {
        $diseaseModel = app(DiseaseModelService::class)->getDiseaseModel($id);
        if ($diseaseModel === null) {
            throw new NotFoundHttpException();
        }
        return $diseaseModel;
    }

    private static function getDiseaseModelVersion(string $version, RouteObject $route): DiseaseModel
    {
        $disease = $route->parameter('disease');
        if (!$disease instanceof Disease) {
            throw new NotFoundHttpException();
        }

        $diseaseModel = app(DiseaseModelService::class)->getDiseaseModelByVersion($disease, $version);
        if ($diseaseModel === null) {
            throw new NotFoundHttpException();
        }

        return $diseaseModel;
    }

    private static function getDiseaseModelUI(string $id): DiseaseModelUI
    {
        $diseaseModelUI = app(DiseaseModelUIService::class)->getDiseaseModelUI($id);
        if ($diseaseModelUI === null) {
            throw new NotFoundHttpException();
        }
        return $diseaseModelUI;
    }

    private static function getDiseaseModelUIVersion(string $version, RouteObject $route): DiseaseModelUI
    {
        $diseaseModel = $route->parameter('diseaseModelVersion');
        if (!$diseaseModel instanceof DiseaseModel) {
            throw new NotFoundHttpException();
        }

        $diseaseModelUI = app(DiseaseModelUIService::class)->getDiseaseModelUIByVersion($diseaseModel, $version);
        if ($diseaseModelUI === null) {
            throw new NotFoundHttpException();
        }

        return $diseaseModelUI;
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', static fn (Request $request): Limit => Limit::perMinute(60));
    }

    private function isNotDeletedOrWithingPurgeWindow(EloquentCase $case): bool
    {
        return $case->deletedAt !== null
            && $case->deletedAt->diff(CarbonImmutable::now())->days > PurgeSoftDeletedModels::PURGE_AFTER_DAYS;
    }
}
