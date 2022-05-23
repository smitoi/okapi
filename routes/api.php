<?php

use App\Http\Controllers\Okapi\Api\InstanceController;
use App\Http\Controllers\Okapi\Api\LoginController;
use App\Http\Controllers\Okapi\Api\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/okapi')->group(static function () {
    Route::name('okapi-users.')->group(function () {
        Route::post('/{role:slug}/register', RegisterController::class)->name('register');
        Route::post('/{role:slug}/login', LoginController::class)->name('login');
    });


    Route::name('okapi-instances.')->middleware('optional-auth-sanctum')->prefix('/{type:slug}')->group(function () {
        Route::get('/', [InstanceController::class, 'index'])->name('index');
        Route::post('/', [InstanceController::class, 'store'])->name('store');

        Route::get('/{instance}', [InstanceController::class, 'show'])->name('show');
        Route::patch('/{instance}', [InstanceController::class, 'update'])->name('update');
        Route::delete('/{instance}', [InstanceController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
