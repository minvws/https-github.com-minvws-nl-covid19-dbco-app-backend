<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CaseController;

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

Route::get('/login', array('as' => 'login', function() {
    return view('login');
}));

Route::get('/colofon', function () {
    return view('welcome');
});

Route::get('/', [CaseController::class, 'listCases'])->middleware('sessionauth');;

Route::get('/case', function () {
    return view('casedetail');
})->middleware('sessionauth');

Route::get('/newcase', [Casecontroller::class, 'newCase'])->middleware('sessionauth');
Route::get('/newcaseedit/{uuid}', [CaseController::class, 'newCaseEdit'])->middleware('sessionauth');

Route::get('auth/identityhub', [LoginController::class, 'redirectToProvider']);
Route::get('auth/login', [LoginController::class, 'handleProviderCallback']);

// Temporary development login stub so you can test the portal without ggd account.
Route::get('auth/stub', [LoginController::class, 'stubAuthenticate']);

Route::post('/savecase', [CaseController::class, 'saveCase'])->middleware('sessionauth');
