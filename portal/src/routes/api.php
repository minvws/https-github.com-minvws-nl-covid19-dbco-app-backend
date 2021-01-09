<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiCaseController;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\Api\ApiTaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where we will register application level API routes.
| Note that this a dedicated, scenario driven API for the vue frontend, so
| We use our nowmal web authentication mechanism.
| For mahine to machine APIs for GGD Contact, see the /src/api application.
|
*/

Route::name('api-')->group(function() {

    // All pages that are behind auth and require user role
    Route::middleware(['auth', 'rolecheck:user'])->group(function () {
        Route::get('/cases/mine', [ApiCaseController::class, 'myCases'])->name('cases-mine');
        Route::get('/case/{caseUuid}', [ApiCaseController::class, 'getCase'])->name('get-case');
        Route::post('/case', [ApiCaseController::class, 'postCase'])->name('post-case');

        Route::get('/cases/{caseUuid}/tasks', [ApiTaskController::class, 'getCaseTasks'])->name('case-tasks');
    });

    // All pages that are behind auth and require planner role
    Route::middleware(['auth', 'rolecheck:planner'])->group(function () {
        Route::get('/cases/all', [ApiCaseController::class, 'allCases'])->name('cases-all');
        Route::get('/users/assignable', [ApiUserController::class, 'assignableUsers'])->name('assignable-users');
        Route::post('/assigncase', [ApiCaseController::class, 'assignCase'])->name('assign-case');
    });

});

