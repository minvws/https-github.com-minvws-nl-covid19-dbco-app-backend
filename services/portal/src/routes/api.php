<?php

declare(strict_types=1);

use App\Helpers\FeatureFlagHelper;
use App\Http\Controllers\Api\ApiAccessRequestController;
use App\Http\Controllers\Api\ApiAssignmentController;
use App\Http\Controllers\Api\ApiBsnController;
use App\Http\Controllers\Api\ApiCallToActionController;
use App\Http\Controllers\Api\ApiCaseAssignmentController;
use App\Http\Controllers\Api\ApiCaseController;
use App\Http\Controllers\Api\ApiCaseFragmentController;
use App\Http\Controllers\Api\ApiCaseLabelController;
use App\Http\Controllers\Api\ApiCaseListController;
use App\Http\Controllers\Api\ApiCaseLockController;
use App\Http\Controllers\Api\ApiCaseMessageController;
use App\Http\Controllers\Api\ApiCaseMetricsController;
use App\Http\Controllers\Api\ApiCaseSyncController;
use App\Http\Controllers\Api\ApiCaseUpdateController;
use App\Http\Controllers\Api\ApiCaseValidationStatusController;
use App\Http\Controllers\Api\ApiCaseValidationStatusMessagesController;
use App\Http\Controllers\Api\ApiCatalogController;
use App\Http\Controllers\Api\ApiContextController;
use App\Http\Controllers\Api\ApiContextFragmentController;
use App\Http\Controllers\Api\ApiExpertQuestionController;
use App\Http\Controllers\Api\ApiHistoryController;
use App\Http\Controllers\Api\ApiHpZoneCopyController;
use App\Http\Controllers\Api\ApiIntakeController;
use App\Http\Controllers\Api\ApiOrganisationController;
use App\Http\Controllers\Api\ApiPlaceController;
use App\Http\Controllers\Api\ApiPlaceVerificationController;
use App\Http\Controllers\Api\ApiPlannerCaseController;
use App\Http\Controllers\Api\ApiSearchController;
use App\Http\Controllers\Api\ApiSessionController;
use App\Http\Controllers\Api\ApiTaskController;
use App\Http\Controllers\Api\ApiTaskFragmentController;
use App\Http\Controllers\Api\Callcenter\ApiCallcenterSearchController;
use App\Http\Controllers\Api\Case\Message\ApiCaseMessageDeleteController;
use App\Http\Controllers\Api\Case\Message\ApiCaseMessageToIndexController;
use App\Http\Controllers\Api\Case\Message\ApiCaseMessageToIndexTemplateController;
use App\Http\Controllers\Api\Case\Message\ApiCaseMessageToTaskController;
use App\Http\Controllers\Api\Case\Message\ApiCaseMessageToTaskTemplateController;
use App\Http\Controllers\Api\Case\TestResult\ApiCaseTestResultController;
use App\Http\Controllers\Api\Disease\ApiDiseaseController;
use App\Http\Controllers\Api\Disease\ApiDiseaseModelController;
use App\Http\Controllers\Api\Disease\ApiDiseaseModelUIController;
use App\Http\Controllers\Api\Dossier\ApiContactController;
use App\Http\Controllers\Api\Dossier\ApiDossierController;
use App\Http\Controllers\Api\Export;
use App\Models\AccessRequest;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Intake;
use App\Models\Eloquent\Place;
use App\Models\PlannerCase\PlannerView;
use Illuminate\Support\Facades\Route;
use MinVWS\Audit\Models\AuditEvent;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where we will register application level API routes.
| Note that this a dedicated, scenario driven API for the vue frontend, so
| We use our nowmal web authentication mechanism.
| For machine to machine APIs for GGD Contact, see the /src/api application.
|
*/

Route::name('api-')->group(static function (): void {
    // All pages that are behind auth and require permissions
    Route::middleware(['auth', 'audit', 'can:listMine,' . EloquentCase::class])->group(static function (): void {
        Route::get('/cases/mine', [ApiCaseController::class, 'myCases'])->name('cases-mine');
        Route::get('/cases/mine/{status}', [ApiCaseController::class, 'myCases'])->name('cases-mine-status');
    });

    Route::middleware(['auth', 'audit', 'can:view,case'])->group(static function (): void {
        Route::get('/case/{case}', [ApiCaseController::class, 'getCase'])->name('get-case');

        Route::prefix('/cases/{case}/fragments')->group(static function (): void {
            Route::get('/', [ApiCaseFragmentController::class, 'getFragments'])->name('get-case-fragments');
        });

        Route::prefix('/cases/{case}/validation-status')->group(static function (): void {
            Route::get('/', [ApiCaseValidationStatusController::class, 'getCaseValidationStatus'])->name('get-case-validation-status');
            Route::get('/messages', [ApiCaseValidationStatusMessagesController::class, 'getCaseValidationStatusMessages'])
                ->name('get-case-validation-status-messages');
        });

        Route::get('/cases/{case}/tasks/{taskGroup}', [ApiTaskController::class, 'getCaseTasks'])->name('case-tasks');

        Route::get('/cases/{case}/contexts', [ApiContextController::class, 'getCaseContexts'])->name('case-contexts');

        Route::prefix('/cases/{case}/testresults')->middleware('audit.object:case,{case}')->group(static function (): void {
            Route::get('', [ApiCaseTestResultController::class, 'getCaseTestResults']);
            Route::post('', [ApiCaseTestResultController::class, 'createManualTestResult']);
            Route::delete('/{testResult}', [ApiCaseTestResultController::class, 'deleteTestResult']);
        });

        Route::get('/copy/{case}/diagnostics', [ApiHpZoneCopyController::class, 'diagnostics'])->name('copy-diagnostics');

        Route::middleware(['auth', 'audit', 'can:viewUserTimeline,case'])->group(static function (): void {
            Route::get('/cases/{case}/timeline', [ApiPlannerCaseController::class, 'getTimeline'])->middleware('audit.object:case,{case}');
        });

        Route::prefix('/cases/{case}/updates')->middleware('featureflag:intake_match_case_enabled')->group(static function (): void {
            Route::get('/', [ApiCaseUpdateController::class, 'listCaseUpdates'])->name('list-case-updates');
            Route::get('/{caseUpdate}', [ApiCaseUpdateController::class, 'getCaseUpdate'])->name('get-case-update');
        });

        Route::prefix('/cases/{case}/messages')->group(static function (): void {
            Route::get('', [ApiCaseMessageController::class, 'getMessages'])->name('case-messages');
            Route::get('{message}', [ApiCaseMessageController::class, 'getMessage'])->name('case-message-details');

            Route::delete('{message}', [ApiCaseMessageDeleteController::class, 'send'])->name('case-messages-delete');

            Route::post('', [ApiCaseMessageToIndexController::class, 'send'])->name('send-message-to-index');
            Route::post('{task}', [ApiCaseMessageToTaskController::class, 'send'])->name('send-message-to-task');

            Route::get('template/{messageTemplateType}', [ApiCaseMessageToIndexTemplateController::class, 'get']);
            Route::get('template/{messageTemplateType}/{task}', [ApiCaseMessageToTaskTemplateController::class, 'get']);
        });

        Route::get('/case/{case}/lock', [ApiCaseLockController::class, 'hasCaseLock'])->name('hasCaseLock');
    });

    Route::middleware(['auth', 'audit', 'can:edit,case'])->group(static function (): void {
        Route::get('/cases/{case}/dump', [ApiCaseController::class, 'dumpCase'])->name('dump-case');
        Route::get('/cases/{case}/check-unanswered-questions', [ApiCaseController::class, 'checkUnansweredQuestions']);
        Route::put('/cases/{case}/pseudo-bsn', [ApiCaseController::class, 'updatePseudoBsn'])->name('put-case-pseudo-bsn');
        Route::get('/cases/{case}/connected', [ApiCaseController::class, 'connected'])->name('get-cases-connected');

        Route::prefix('/cases/{case}/updates')->middleware('featureflag:intake_match_case_enabled')->group(static function (): void {
            Route::post('/{caseUpdate}/apply', [ApiCaseUpdateController::class, 'applyCaseUpdate'])->name('apply-case-update');
        });

        Route::prefix('/cases/{case}/fragments')->group(static function (): void {
            Route::put('/', [ApiCaseFragmentController::class, 'updateFragments'])->name('put-case-fragments');
            Route::get('/{fragmentName}', [ApiCaseFragmentController::class, 'getFragment'])->name('get-case-fragment');
            Route::put('/{fragmentName}', [ApiCaseFragmentController::class, 'updateFragment'])->name('put-case-fragment');
        });

        Route::post('/case/{case}/expertQuestion', [ApiExpertQuestionController::class, 'create'])->name('store-expertQuestion');
        Route::put('/case/{case}/expertQuestion/{expertQuestion}', [ApiExpertQuestionController::class, 'update'])->name(
            'update-expertQuestion',
        );

        Route::post('/case/{case}/lock/refresh', [ApiCaseLockController::class, 'refreshCaseLock'])->name('refreshCaseLock');
        Route::delete('/case/{case}/lock/remove', [ApiCaseLockController::class, 'removeCaseLock'])->name('hasCaseLock');
    });

    /**
     * Block for routes where the user has either 'user edit' or 'planner edit' case access.
     */
    Route::middleware(['auth', 'audit', 'can:editAsUserOrAsPlanner,case'])->group(static function (): void {
        Route::middleware(['can:editContactStatus,' . EloquentCase::class])->group(static function (): void {
            Route::put('/cases/{case}/contact-status', [ApiCaseController::class, 'updateContactStatus'])
                ->name('put-update-contact-status')
                ->middleware('audit.object:case,{case}');
        });
    });

    /**
     * Block for routes where the user has either 'user view' or 'planner edit' case access.
     */
    Route::middleware(['auth', 'audit', 'can:viewAsUserOrAsPlanner,case'])->group(static function (): void {
        Route::middleware(['can:editContactStatus,' . EloquentCase::class])->group(static function (): void {
            Route::get('/cases/{case}/contact-status', [ApiCaseController::class, 'getContactStatus'])
                ->name('get-contact-status')
                ->middleware('audit.object:case,{case}');
        });

        Route::get('/cases/{case}/history/osiris', [ApiHistoryController::class, 'osiris'])->middleware([
            'can:caseViewOsirisHistory,case',
            'audit.object:case,{case}',
        ]);
    });

    Route::middleware(['auth', 'audit', 'can:editBcoPhase,' . EloquentCase::class])->group(static function (): void {
        Route::put('/cases/multi/bcophase', [ApiCaseController::class, 'updateCaseBcoPhaseMulti'])->name('put-case-phase-multi');
    });

    Route::middleware(['auth', 'audit', 'can:editBcoPhase,case'])->group(static function (): void {
        Route::put('/cases/{case}/bcophase', [ApiCaseController::class, 'updateCaseBcoPhase'])->name('put-case-phase');
    });

    Route::middleware(['auth', 'audit', 'can:addressLookup,' . EloquentCase::class])->group(static function (): void {
        Route::get('/addresses', [ApiPlaceController::class, 'addressLookup'])->name('address-lookup');
    });

    Route::middleware(['auth', 'audit', 'can:search,' . Place::class])->group(static function (): void {
        Route::match(['GET', 'POST'], '/places/search', [ApiPlaceController::class, 'searchPlaces'])->name('places-list');
        Route::match(['GET', 'POST'], '/places/search/similar', [ApiPlaceController::class, 'searchSimilarPlaces']);
    });

    Route::middleware(['auth', 'audit', 'can:create,' . Place::class])->group(static function (): void {
        Route::post('/places', [ApiPlaceController::class, 'createPlace'])->name('places-create');
    });

    Route::middleware(['auth', 'audit', 'can:caseArchiveDirectly,' . EloquentCase::class])->group(static function (): void {
        Route::put('/cases/archiveMulti', [ApiCaseController::class, 'archiveCaseDirectlyMulti'])
            ->name('put-archive-case-multiple');

        Route::put('/cases/{case}/archive', [ApiCaseController::class, 'archiveCaseDirectly'])
            ->name('put-archive-case')
            ->middleware('audit.object:case,{case}');
    });

    Route::middleware(['auth', 'audit', 'can:caseReopen'])->group(static function (): void {
        Route::patch('/cases/{case}/reopen', [ApiCaseController::class, 'reopenCase']);
    });

    Route::middleware(['auth', 'audit', 'can:updateOrganisation,case'])->group(static function (): void {
        Route::post('/case/{case}/update-organisation', [ApiCaseController::class, 'updateCaseOrganisation']);
    });

    Route::middleware(['auth', 'audit', 'can:verify,place'])->group(static function (): void {
        Route::put('/places/{place}/verify', [ApiPlaceVerificationController::class, 'verifyPlace'])->middleware(
            ['audit.object:place,{place}'],
        );
        Route::put('/places/{place}/unverify', [ApiPlaceVerificationController::class, 'unVerifyPlace'])->middleware(
            'audit.object:place,{place}',
        );
    });
    Route::middleware(['auth', 'audit'])->group(static function (): void {
        Route::put('/places/verifyMulti', [ApiPlaceVerificationController::class, 'verifyPlaceMulti']);
    });

    // Only 'case edit' permission is needed to execute actions below
    Route::middleware(['auth', 'audit', 'can:edit,' . EloquentCase::class])->group(static function (): void {
        Route::get('/places/{place}', [ApiPlaceController::class, 'getPlace'])->name('get-place')->whereUuid('place');

        Route::get('/casequeues/{caseQueue}/next', [ApiCaseAssignmentController::class, 'nextCase'])
            ->middleware(['audit:' . AuditEvent::ACTION_EXECUTE, 'audit.object:caselist,{caseQueue}', 'can:caseCanPickUpNew']);
    });

    Route::middleware(['auth', 'audit', 'audit.object:place,{place}'])->group(static function (): void {
        Route::get('/places/{place}/cases', [ApiPlaceController::class, 'getPlaceCases'])
            ->name('cases-list')->middleware('can:placeCaseList,place');

        Route::get('/places/{place}/sections', [ApiPlaceController::class, 'getPlaceSections'])
            ->name('sections-list')->middleware('can:sectionList,place');

        Route::put('/places/{place}/sections', [ApiPlaceController::class, 'createPlaceSection'])
            ->name('sections-store')->middleware(['can:sectionCreate,place']);

        Route::patch('/places/{place}/sections', [ApiPlaceController::class, 'updatePlaceSection'])
            ->name('sections-store')->middleware(['can:sectionEdit,place']);

        Route::post('/places/{place}/sections/{section}/merge', [ApiPlaceController::class, 'mergePlaceSection'])
            ->name('sections-merge')->middleware('can:sectionMerge,place');
    });

    Route::middleware(['auth', 'audit', 'can:list,' . CaseLabel::class])->group(static function (): void {
        Route::get('caselabels', [ApiCaseLabelController::class, 'getCaseLabels'])->name('get-case-labels');
    });

    Route::middleware(['auth', 'audit', 'can:bsnLookup,' . EloquentCase::class])->group(static function (): void {
        Route::post('/pseudo-bsn/lookup', [ApiBsnController::class, 'pseudoBsnLookup'])->name('bsn-lookup');
    });

    Route::middleware(['auth', 'audit', 'can:export,' . EloquentCase::class])->group(static function (): void {
        Route::post('/markascopied', [ApiCaseController::class, 'markAsCopied']);
    });

    Route::post('/cases/{case}/tasks', [ApiTaskController::class, 'createTask'])
        ->name('case-task-create')
        ->middleware(['auth', 'audit', 'can:create,' . EloquentTask::class, 'can:edit,case']);
    Route::delete('/tasks/{task}', [ApiTaskController::class, 'deleteTask'])
        ->name('task-delete')
        ->middleware(['auth', 'audit', 'can:delete,task']);

    Route::middleware(['auth', 'audit', 'audit.object:task,{task}', 'can:edit,task'])->group(static function (): void {
        Route::put('/tasks/{task}', [ApiTaskController::class, 'updateTask'])->name('task-update');
        Route::put('/tasks/{task}/pseudo-bsn', [ApiTaskController::class, 'updatePseudoBsn'])->name('put-task-pseudo-bsn');
    });

    Route::middleware(['auth', 'audit', 'can:edit,task'])->group(static function (): void {
        Route::get('/tasks/{task}/connected', [ApiTaskController::class, 'getConnectedTasks'])->name('get-tasks-connected');
    });

    Route::middleware(['auth', 'audit', 'can:view,task'])->group(static function (): void {
        Route::prefix('/tasks/{task}/fragments')->group(static function (): void {
            Route::get('/', [ApiTaskFragmentController::class, 'getFragments'])->name('get-task-fragments');
            Route::get('/{fragmentName}', [ApiTaskFragmentController::class, 'getFragment'])->name('get-task-fragment');
        });
    });

    Route::middleware(['auth', 'audit', 'can:edit,task'])->group(static function (): void {
        Route::prefix('/tasks/{task}/fragments')->group(static function (): void {
            Route::put('/', [ApiTaskFragmentController::class, 'updateFragments'])->name('put-task-fragments');
            Route::put('/{fragmentName}', [ApiTaskFragmentController::class, 'updateFragment'])->name('put-task-fragment');
        });
    });

    Route::middleware(['auth', 'audit', 'can:create,' . Context::class, 'can:edit,case'])->group(static function (): void {
        Route::post('/cases/{case}/contexts', [ApiContextController::class, 'createContext'])
            ->name('case-context-create');
    });

    Route::middleware(['auth', 'audit', 'can:view,context'])->group(static function (): void {
        Route::get('/contexts/{context}/sections', [ApiContextController::class, 'getContextSections'])
            ->name('context-sections-get')
            ->middleware('audit.object:context,{context}');
        Route::prefix('/contexts/{context}/fragments')->group(static function (): void {
            Route::get('/', [ApiContextFragmentController::class, 'getFragments'])->name('get-context-fragments');
            Route::get('/{fragmentName}', [ApiContextFragmentController::class, 'getFragment'])->name('get-context-fragment');
        });
    });

    Route::middleware(['auth', 'audit', 'can:edit,context'])->group(static function (): void {
        Route::put('/contexts/{context}', [ApiContextController::class, 'updateContext'])
            ->name('context-update')
            ->middleware('audit.object:context,{context}');
        Route::post('/contexts/{context}/sections/{section}', [ApiContextController::class, 'storeContextSection'])
            ->name('context-sections-create');
        Route::post('/contexts/{context}/place/{place}', [ApiContextController::class, 'linkPlaceToContext'])
            ->name('link-place-to-context')
            ->middleware('can:link,context');
        Route::prefix('/contexts/{context}/fragments')->group(static function (): void {
            Route::put('/', [ApiContextFragmentController::class, 'updateFragments'])->name('put-context-fragments');
            Route::put('/{fragmentName}', [ApiContextFragmentController::class, 'updateFragment'])->name('put-context-fragment');
        });
    });

    Route::middleware(['auth', 'audit', 'can:delete,context'])->group(static function (): void {
        Route::delete('/contexts/{context}', [ApiContextController::class, 'deleteContext'])
            ->name('context-delete')
            ->middleware('audit.object:context,{context}');
        Route::delete('/contexts/{context}/sections/{section}', [ApiContextController::class, 'deleteContextSection'])
            ->name('context-sections-delete');
        Route::delete('/contexts/{context}/place/{place}', [ApiContextController::class, 'unlinkPlaceFromContext'])
            ->name('unlink-place-from-context')
            ->middleware('can:link,context');
    });

    Route::middleware(['auth', 'audit', 'can:create,' . EloquentCase::class])->group(static function (): void {
        Route::post('/cases', [ApiPlannerCaseController::class, 'createCase']);
    });

    Route::middleware(['auth', 'audit', 'can:basicEdit,case'])->group(static function (): void {
        Route::get('/cases/planner/{case}', [ApiPlannerCaseController::class, 'getCase'])->middleware('audit.object:case,{case}');
        Route::put('/cases/planner/{case}', [ApiPlannerCaseController::class, 'updateCase']);
    });

    Route::middleware(['auth', 'audit', 'can:editMeta,case'])->group(static function (): void {
        Route::put('/cases/planner/{case}/meta', [ApiPlannerCaseController::class, 'updateCaseMeta']);
    });

    Route::middleware(['auth', 'audit', 'can:createNote,case'])->group(static function (): void {
        Route::post('/cases/{case}/notes', [ApiCaseController::class, 'createNote'])->middleware('audit.object:case,{case}');
    });

    Route::middleware(['auth', 'audit', 'can:search,' . EloquentCase::class])->group(static function (): void {
        Route::post('/cases/planner/search', [ApiPlannerCaseController::class, 'searchCase']);
    });

    Route::middleware(['auth', 'audit', 'can:softDelete,case'])->group(static function (): void {
        Route::delete('/cases/{case}', [ApiPlannerCaseController::class, 'deleteCase'])->middleware('audit.object:case,{case}');
    });

    Route::middleware(['auth', 'audit', 'can:list,' . EloquentCase::class])->group(static function (): void {
        Route::get('/cases/counts', [ApiPlannerCaseController::class, 'countCases']);
        Route::get('/cases/{view}', [ApiPlannerCaseController::class, 'listCases'])->where('view', implode('|', PlannerView::allValues()));
        Route::get('/caselists/{caseList}/cases/counts', [ApiPlannerCaseController::class, 'countCases']);
        Route::get('/caselists/{caseList}/cases/{view}', [ApiPlannerCaseController::class, 'listCases'])->where(
            'view',
            implode('|', PlannerView::onlyValuesForCaseList()),
        );
        Route::put('/cases/priority', [ApiPlannerCaseController::class, 'updatePriority']);

        Route::get('/caselists', [ApiCaseListController::class, 'listCaseLists']);
        Route::post('/caselists', [ApiCaseListController::class, 'createCaseList']);
        Route::get('/caselists/{caseListUuid}', [ApiCaseListController::class, 'getCaseList'])->middleware(
            'audit.object:caselist,{caseListUuid}',
        );
        Route::put('/caselists/{caseList}', [ApiCaseListController::class, 'updateCaseList'])->middleware(
            'audit.object:caselist,{caseList}',
        );
        Route::delete('/caselists/{caseList}', [ApiCaseListController::class, 'deleteCaseList'])->middleware(
            'audit.object:caselist,{caseList}',
        );

        Route::get('/cases/{case}/assignment/options', [ApiCaseAssignmentController::class, 'getAssignmentOptions'])->middleware(
            'audit.object:case,{case}',
        );
        Route::post('/cases/{case}/assignment/options', [ApiCaseAssignmentController::class, 'getAssignmentOptions'])->middleware(
            'audit.object:case,{case}',
        );
        Route::put('/cases/{case}/assignment', [ApiCaseAssignmentController::class, 'updateAssignment'])->middleware(
            'audit.object:case,{case}',
        );
        Route::get('/cases/assignment/options', [ApiCaseAssignmentController::class, 'getAssignmentOptionsMulti']);
        Route::post('/cases/assignment/options', [ApiCaseAssignmentController::class, 'getAssignmentOptionsMulti']);
        Route::get('/cases/assignment/all-user-options', [ApiCaseAssignmentController::class, 'getuserAssignmentOptions']);
        Route::put('/cases/assignment', [ApiCaseAssignmentController::class, 'updateAssignmentMulti']);

        Route::get('/sync/{case}/fragments', [ApiCaseSyncController::class, 'getCaseFragments']);
    });

    Route::middleware(['auth', 'audit', 'can:organisationList'])->group(static function (): void {
        Route::get('/organisations', [ApiOrganisationController::class, 'listOrganisations']);
    });

    Route::middleware(['auth', 'audit', 'can:organisationUpdate'])->group(static function (): void {
        Route::put('/organisations/current', [ApiOrganisationController::class, 'updateCurrentOrganisation']);
        Route::patch('/organisation/current/bcophase', [ApiOrganisationController::class, 'updateCurrentOrganisationBcoPhase']);
    });

    Route::middleware(['auth', 'audit', 'can:viewPlannerTimeline,case'])->group(static function (): void {
        Route::get('/cases/{case}/planner-timeline', [ApiPlannerCaseController::class, 'getPlannerTimeline'])->middleware(
            'audit.object:case,{case}',
        );
    });

    Route::middleware(['auth', 'audit'])->group(static function (): void {
        Route::middleware('can:list,' . AccessRequest::class)->group(static function (): void {
            Route::post('/search', [ApiSearchController::class, 'search'])
                ->name('search');
        });

        Route::middleware('can:viewAccessRequest,softDeletedCase')->group(static function (): void {
            Route::get('/access-requests/case/{softDeletedCase}/download', [ApiAccessRequestController::class, 'downloadCase'])
                ->name('access-requests-download-case');
            Route::get('/access-requests/case/{softDeletedCase}/download/html', [ApiAccessRequestController::class, 'downloadCaseHtml'])
                ->name('access-requests-download-case-html');
            Route::get('/access-requests/case/{softDeletedCase}/fragments', [ApiAccessRequestController::class, 'fragmentsCase'])
                ->name('access-requests-fragments-case')
                ->middleware('audit.object:access-request,{softDeletedCase}');
        });

        Route::middleware('can:viewAccessRequest,task')->group(static function (): void {
            Route::get('/access-requests/task/{task}/download', [ApiAccessRequestController::class, 'downloadTask'])
                ->name('access-requests-download-task')
                ->middleware('audit.object:access-request-task,{task}');
            Route::get('/access-requests/task/{task}/download/html', [ApiAccessRequestController::class, 'downloadTaskHtml'])
                ->name('access-requests-download-task-html')
                ->middleware('audit.object:access-request-task,{task}');
        });

        Route::middleware('can:viewAccessRequest,softDeletedTask')->group(static function (): void {
            Route::get('/access-requests/task/{softDeletedTask}/fragments', [ApiAccessRequestController::class, 'fragmentsTask'])
                ->name('access-requests-fragments-task')
                ->middleware('audit.object:access-request-task,{softDeletedTask}');
        });

        Route::middleware('can:hardDelete,case')->group(static function (): void {
            Route::delete('/access-requests/case/{case}', [ApiAccessRequestController::class, 'deleteCase'])
                ->name('access-requests-delete-case')
                ->middleware('audit.object:case,{case}');
        });

        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // todo, can we user delete,task here?
        Route::middleware('can:delete,task')->group(static function (): void {
            Route::delete('/access-requests/task/{task}', [ApiAccessRequestController::class, 'deleteTask'])
                ->name('access-requests-delete-task')
                ->middleware('audit.object:task,{task}');
        });

        Route::middleware('can:restore,softDeletedCase')->group(static function (): void {
            Route::post('/access-requests/case/{softDeletedCase}/restore', [ApiAccessRequestController::class, 'restoreCase'])
                ->name('access-requests-restore-case')
                ->middleware('audit.object:case,{softDeletedCase}');
        });

        Route::middleware('can:restore,softDeletedTask')->group(static function (): void {
            Route::post('/access-requests/task/{softDeletedTask}/restore', [ApiAccessRequestController::class, 'restoreTask'])
                ->name('access-requests-restore-task')
                ->middleware('audit.object:task,{softDeletedTask}');
        });
    });

    Route::middleware(['auth', 'audit', 'can:list,' . Place::class])->group(static function (): void {
        Route::post('/places/{place}/cluster/reset', [ApiPlaceController::class, 'resetCount']);
    });

    Route::middleware(['auth', 'audit', 'can:edit,place'])->group(static function (): void {
        Route::put('/places/{place}', [ApiPlaceController::class, 'updatePlace'])->name('update-place');
    });

    Route::middleware(['auth', 'audit', 'audit.object:place,{place}', 'can:merge,place'])->group(static function (): void {
        Route::put('/places/{place}/merge', [ApiPlaceController::class, 'mergePlace'])->name('merge-place');
    });

    Route::middleware(['featureflag:catalog_enabled', 'audit'])->group(static function (): void {
        Route::get('/catalog', [ApiCatalogController::class, 'index'])->name('catalog');
        Route::get('/catalog/{class}/{version}', [ApiCatalogController::class, 'show'])->name('catalogType');
    });

    Route::middleware(['auth', 'audit', 'can:list,' . Intake::class])->group(static function (): void {
        Route::get('/intakes', [ApiIntakeController::class, 'listIntakes']);
    });

    Route::prefix('/expert-questions')->middleware(['auth', 'audit'])->group(static function (): void {
        // Listing
        Route::middleware('can:list,' . ExpertQuestion::class)->group(static function (): void {
            Route::get(
                '/',
                [ApiExpertQuestionController::class, 'listExpertQuestions'],
            ); // pass through query "view" with "ExpertQuestionType" enum
        });

        // Getting
        Route::middleware('can:get,expertQuestion')->group(static function (): void {
            Route::get('/{expertQuestion}', [ApiExpertQuestionController::class, 'getExpertQuestion']);
        });

        Route::middleware('can:getWithoutBinding,' . ExpertQuestion::class)->group(static function (): void {
            Route::post('/find-by-case-id', [ApiExpertQuestionController::class, 'findExpertQuestionByCaseId']);
        });

        // Assigning
        Route::middleware('can:assign,expertQuestion')->group(static function (): void {
            Route::post('/{expertQuestion}/assignment', [ApiExpertQuestionController::class, 'assignExpertQuestion']);
            Route::delete('/{expertQuestion}/assignment', [ApiExpertQuestionController::class, 'unassignExpertQuestion']);
        });

        // Answering
        Route::middleware('can:answer,expertQuestion')->group(static function (): void {
            Route::put('/{expertQuestion}/answer', [ApiExpertQuestionController::class, 'answerExpertQuestion']);
        });
    });

    Route::prefix('/call-to-actions')->middleware(['auth', 'audit'])->group(static function (): void {
        // Listing
        Route::middleware('can:choreList')->group(static function (): void {
            Route::get('/', [ApiCallToActionController::class, 'listCallToActions']);
            Route::get('/{callToActionUuid}/history', [ApiCallToActionController::class, 'listCallToActionHistory']);
        });

        // Getting
        Route::middleware('can:get,callToAction')->group(static function (): void {
            Route::get('/{callToAction}', [ApiCallToActionController::class, 'getCallToAction']);
        });

        Route::middleware('can:create,' . CallToAction::class)->group(static function (): void {
            Route::put('/', [ApiCallToActionController::class, 'createCallToAction']);
        });

        Route::post('/{callToAction}/pickup', [ApiCallToActionController::class, 'pickupCallToAction']);
        Route::post('/{callToAction}/drop', [ApiCallToActionController::class, 'dropCallToAction']);
        Route::post('/{callToAction}/complete', [ApiCallToActionController::class, 'completeCallToAction']);
    });

    Route::prefix('/callcenter')->middleware(['auth', 'audit'])->group(static function (): void {
        Route::middleware('can:callcenterView')->group(static function (): void {
            Route::post('/search', [ApiCallcenterSearchController::class, 'search']);
        });
    });

    Route::prefix('/assignment')->middleware(['auth', 'audit', 'can:addCasesByChore'])->group(static function (): void {
        Route::post('/cases/{case}', [ApiAssignmentController::class, 'assignSingleCase']);
    });

    Route::post('/session-refresh', [ApiSessionController::class, 'refresh'])
        ->middleware(['extend-inactivity-timer'])->block();

    Route::prefix('/export')->middleware(['auth:export', 'audit'])->group(static function (): void {
        Route::get('/schemas/{path}', [Export\ApiSchemaController::class, 'show'])
            ->where('path', '[\w\s\-_\/]+')
            ->name('export-json-schema');

        Route::get('/cases/', [Export\ApiExportCaseController::class, 'index'])->name('export-cases');
        Route::get('/cases/{pseudoId}', [Export\ApiExportCaseController::class, 'show'])->name('export-case');

        Route::get('/places/', [Export\ApiExportPlaceController::class, 'index'])->name('export-places');
        Route::get('/places/{pseudoId}', [Export\ApiExportPlaceController::class, 'show'])->name('export-place');

        Route::get('/events/', [Export\ApiExportEventController::class, 'index'])->name('export-events');
        Route::get('/events/{pseudoId}', [Export\ApiExportEventController::class, 'show'])->name('export-event');
    });

    Route::prefix('/cases/metrics')->middleware(['auth', 'audit', 'can:caseMetricsList,' . EloquentCase::class])->name(
        'cases-metrics-',
    )->group(
        static function (): void {
            Route::prefix('/created-archived')->group(static function (): void {
                Route::get('/', [ApiCaseMetricsController::class, 'getCreatedArchived'])
                    ->name('created-archived');
                Route::get('/is-refreshing', [ApiCaseMetricsController::class, 'getCreatedArchivedIsRefreshing'])
                    ->name('created-archived-is-refreshing');
                Route::post('/refresh', [ApiCaseMetricsController::class, 'refreshCreatedArchived'])
                    ->name('created-archived-refresh');
            });
        },
    );

    // diseases / dossiers; work in progress
    Route::middleware(['audit'])->group(static function (): void {
        if (!FeatureFlagHelper::isEnabled('diseases_and_dossiers_enabled')) {
            return;
        }

        Route::prefix('diseases')->group(static function (): void {
            Route::get('', [ApiDiseaseController::class, 'index'])->name('disease-index');
            Route::post('', [ApiDiseaseController::class, 'create'])->name('disease-create');

            Route::get('active', [ApiDiseaseController::class, 'active'])->name('disease-active');

            Route::prefix('{disease}')->group(static function (): void {
                Route::get('', [ApiDiseaseController::class, 'show'])->name('disease-show');
                Route::put('', [ApiDiseaseController::class, 'update'])->name('disease-update');
                Route::delete('', [ApiDiseaseController::class, 'delete'])->name('disease-delete');

                Route::prefix('models')->group(static function (): void {
                    Route::get('', [ApiDiseaseModelController::class, 'index'])->name('disease-model-index');
                    Route::post('', [ApiDiseaseModelController::class, 'create'])->name('disease-model-create');

                    Route::prefix('{diseaseModelVersion}')->group(static function (): void {
                        Route::get('forms/{entityName}', [ApiDiseaseModelController::class, 'showForm'])->name('disease-model-show-form');
                        Route::get(
                            'uis/{diseaseModelUIVersion}/forms/{entityName}',
                            [ApiDiseaseModelUIController::class, 'showForm'],
                        )->name(
                            'disease-model-ui-show-form',
                        );

                        Route::prefix('dossiers')->group(static function (): void {
                            Route::post('', [ApiDossierController::class, 'create'])->name('dossier-create');
                            Route::patch('validate', [ApiDossierController::class, 'validateForCreate'])->name('dossier-validate-create');
                        });
                    });
                });
            });
        });

        Route::prefix('disease-models/{diseaseModel}')->group(static function (): void {
            Route::get('', [ApiDiseaseModelController::class, 'show'])->name('disease-model-show');
            Route::put('', [ApiDiseaseModelController::class, 'update'])->name('disease-model-update');
            Route::delete('', [ApiDiseaseModelController::class, 'delete'])->name('disease-model-delete');
            Route::patch('publish', [ApiDiseaseModelController::class, 'publish'])->name('disease-model-publish');
            Route::patch('archive', [ApiDiseaseModelController::class, 'archive'])->name('disease-model-archive');
            Route::patch('clone', [ApiDiseaseModelController::class, 'clone'])->name('disease-model-clone');

            Route::prefix('uis')->group(static function (): void {
                Route::get('', [ApiDiseaseModelUIController::class, 'index'])->name('disease-model-ui-index');
                Route::post('', [ApiDiseaseModelUIController::class, 'create'])->name('disease-model-ui-create');
            });
        });

        Route::prefix('disease-model-uis/{diseaseModelUI}')->group(static function (): void {
            Route::get('', [ApiDiseaseModelUIController::class, 'show'])->name('disease-model-ui-show');
            Route::put('', [ApiDiseaseModelUIController::class, 'update'])->name('disease-model-ui-update');
            Route::delete('', [ApiDiseaseModelUIController::class, 'delete'])->name('disease-model-ui-delete');
            Route::patch('publish', [ApiDiseaseModelUIController::class, 'publish'])->name('disease-model-ui-publish');
            Route::patch('archive', [ApiDiseaseModelUIController::class, 'archive'])->name('disease-model-ui-archive');
            Route::patch('clone', [ApiDiseaseModelUIController::class, 'clone'])->name('disease-model-ui-clone');
        });

        Route::prefix('dossiers/{dossier}')->group(static function (): void {
            Route::get('', [ApiDossierController::class, 'show'])->name('dossier-show');
            Route::put('', [ApiDossierController::class, 'update'])->name('dossier-update');
            Route::patch('validate', [ApiDossierController::class, 'validateForUpdate'])->name('dossier-validate-update');

            Route::prefix('contacts')->group(static function (): void {
                Route::get('', [ApiContactController::class, 'index'])->name('contact-index');
                Route::get('new', [ApiContactController::class, 'index'])->name('contact-new');
                Route::post('', [ApiContactController::class, 'create'])->name('contact-create');
                Route::patch('validate', [ApiContactController::class, 'validateForCreate'])->name('contact-validate-create');
            });
        });

        Route::prefix('contacts/{dossierContact}')->group(static function (): void {
            Route::get('', [ApiContactController::class, 'show'])->name('contact-show');
            Route::put('', [ApiContactController::class, 'update'])->name('contact-update');
            Route::delete('', [ApiContactController::class, 'delete'])->name('contact-delete');
            Route::patch('validate', [ApiContactController::class, 'validateForUpdate'])->name('contact-validate-update');
        });
    });
});
