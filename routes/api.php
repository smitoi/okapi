<?php

use App\Http\Controllers\Okapi\Api\InstanceController;
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
    Route::name('okapi-instances.')->prefix('/{type:slug}')->group(function () {
        Route::get('/', [InstanceController::class, 'index'])->name('index');
        Route::post('/', [InstanceController::class, 'store'])->name('store');

        Route::get('/{instance}', [InstanceController::class, 'show'])->name('show');
        Route::put('/{instance}', [InstanceController::class, 'update'])->name('update');
        Route::delete('/{instance}', [InstanceController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
