<?php

declare(strict_types=1);

use App\Helpers\Environment;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CallcenterController;
use App\Http\Controllers\CallToActionController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\CaseMetricsController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\ConversationCoachController;
use App\Http\Controllers\MedicalSupervisorController;
use App\Http\Controllers\Osiris\SoapMockServiceController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\PlaygroundController;
use App\Http\Controllers\RootController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [LoginController::class, 'login'])
    ->name('login')
    ->middleware(['remove-inactivity-timer']);

// All pages that are behind auth and require a permission
Route::middleware(['auth', 'consent', 'audit', 'extend-inactivity-timer'])->group(static function (): void {
    // Home (redirecting to overview pages depending on role, no permission required)
    Route::get('/', [RootController::class, 'rootRedirect'])->name('root-redirect');
    // Case overview
    Route::middleware('can:listMine,' . EloquentCase::class)
        ->get('/cases', [CaseController::class, 'listUserCases'])
        ->name('cases');
    Route::get('/consent', [ConsentController::class, 'showConsent'])->name('consent-show');
    Route::post('/consent', [ConsentController::class, 'storeConsent'])->name('consent-store');

    Route::middleware(['can:list,' . EloquentCase::class])->group(static function (): void {
        Route::get('/planner/{listId?}', [PlannerController::class, 'listCases'])->name('planner');
    });

    Route::middleware(['can:caseMetricsList', 'featureflag:case_metrics_enabled'])->group(static function (): void {
        Route::get('/casemetrics', [CaseMetricsController::class, 'listCaseMetrics'])
            ->name('case-metrics');
    });

    Route::middleware(['featureflag:catalog_enabled'])->group(static function (): void {
        Route::get('/catalog', [CatalogController::class, 'listCatalog'])
            ->name('catalog');
    });

    Route::prefix('/compliance')->group(static function (): void {
        Route::get('', [ComplianceController::class, 'listAccessRequests'])
            ->name('compliance')
            ->middleware('can:listAccessRequests,' . EloquentCase::class);
        Route::get('/search', [ComplianceController::class, 'viewSearchResults'])
            ->name('compliance-search')
            ->middleware('can:listAccessRequests,' . EloquentCase::class);
    });

    // Open case in BCO view
    Route::get('/editcase/{case}', [CaseController::class, 'editCase'])
        ->name('case-edit')
        ->middleware('can:view,case');

    // Open place in detail view
    Route::get('/editplace/{place}', [PlaceController::class, 'editPlace'])
        ->name('place-edit')
        ->middleware('can:placeEdit');

    // Places overview
    Route::get('/places/{view?}', [PlaceController::class, 'listPlaces'])
        ->name('places')
        ->middleware('can:list,' . Place::class);

    Route::post('/task/{task}/questionnaire', [TaskController::class, 'saveTaskQuestionnaire'])
        ->name('task-questionnaire-save')
        ->middleware('can:edit,task');

    // Medical supervision
    Route::get('/medische-supervisie', [MedicalSupervisorController::class, 'index'])
        ->name('medical-supervisor-index')
        ->middleware('can:expertQuestionMedicalSupervisor');

    // Conversation coach
    Route::get('/gesprekscoach', [ConversationCoachController::class, 'index'])
        ->name('conversation-coach-index')
        ->middleware('can:expertQuestionConversationCoach');

    // Callcenter
    Route::get('/dossierzoeken', [CallcenterController::class, 'search'])
        ->name('callcenter-search')
        ->middleware(['can:callcenterView']);

    // Taken
    Route::get('/taken', [CallToActionController::class, 'index'])
        ->name('call-to-action-index')
        ->middleware('can:caseViewCallToAction');
    Route::get('/editcase/{case}/tasks/new', [CallToActionController::class, 'create'])
        ->name('call-to-action-create')
        ->middleware(['can:view,case', 'can:caseCreateCallToAction']);

    // Admin
    Route::middleware('featureflag:admin_view_enabled')->group(static function (): void {
        Route::get('/beheren', [AdminController::class, 'index'])
            ->name('admin')
            ->middleware('can:adminView');

        // Admin - Policy Advice
        Route::middleware('can:adminPolicyAdviceModule')->group(static function (): void {
            Route::get('/beheren/beleidsversies', [AdminController::class, 'index'])
                ->name('admin-policy-advice');
            Route::get('/beheren/beleidsversies/{policy}', [AdminController::class, 'index'])
                ->name('admin-policy-advice');
            Route::get('/beheren/beleidsversies/{policy}/kalender-views/{view}', [AdminController::class, 'index'])
                ->name('admin-policy-advice');
            Route::get('/beheren/beleidsversies/{policy}/richtlijnen/{guideline}', [AdminController::class, 'index'])
                ->name('admin-policy-advice');
        });
    });
});

// All pages that are behind auth only
Route::middleware('auth')->group(static function (): void {
    Route::get('/logout', [LoginController::class, 'logout'])
        ->name('user-logout')
        ->middleware(['audit', 'remove-inactivity-timer'])
        ->block();

    // Account
    Route::get('/profile', [UserController::class, 'profile'])->name('user-profile');
    // Privacy statement
    Route::get('/consent/privacy', [ConsentController::class, 'showPrivacyStatement'])
        ->name('privacy-statement-show')
        ->middleware(['remove-inactivity-timer']);
});


Route::get('auth/identityhub', [LoginController::class, 'redirectToProvider']);
Route::get('auth/login', [LoginController::class, 'handleProviderCallback'])->name('provider-login-callback');
Route::post('consent/reset', [ConsentController::class, 'resetConsent'])->name('consent-reset');

// Temporary development login stub so you can test the portal without ggd account.
if (config('auth.allow_demo_login')) {
    Route::get('auth/stub', [LoginController::class, 'stubAuthenticate'])->name('stub-login');
}

if (Environment::isDevelopment()) {
    Route::get('/playground', [PlaygroundController::class, 'viewPlayground'])
    ->name('playground');
}

if (config('services.osiris.use_mock_client')) {
    Route::get('/osiris/wsdl', [SoapMockServiceController::class, 'getWsdl'])
        ->name('osiris-mock-wsdl');
    Route::post('/osiris', [SoapMockServiceController::class, 'handleRequest'])
        ->name('osiris-mock-server');
}
