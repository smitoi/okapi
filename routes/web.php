<?php

use App\Http\Controllers\Okapi\ApiKeyController;
use App\Http\Controllers\Okapi\DocumentationController;
use App\Http\Controllers\Okapi\InstanceController;
use App\Http\Controllers\Okapi\TypeController;
use App\Http\Controllers\Okapi\RoleController;
use App\Http\Controllers\Okapi\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', static function () {
    return redirect(route('okapi-types.index'));
});

Route::prefix('/okapi')->middleware(['auth', 'verified', 'role:Admin'])->group(static function () {
    Route::get('/documentation', DocumentationController::class)->name('okapi-documentation');

    Route::name('okapi-roles.')->prefix('/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/new', [RoleController::class, 'create'])->name('create');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::name('okapi-api-keys.')->prefix('/api-keys')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index'])->name('index');
        Route::get('/new', [ApiKeyController::class, 'create'])->name('create');
        Route::get('/{apiKey}', [ApiKeyController::class, 'show'])->name('show');
        Route::get('/{apiKey}/edit', [ApiKeyController::class, 'edit'])->name('edit');
        Route::put('/{apiKey}', [ApiKeyController::class, 'update'])->name('update');
        Route::post('/', [ApiKeyController::class, 'store'])->name('store');
        Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
    });

    Route::name('okapi-users.')->prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/new', [UserController::class, 'create'])->name('create');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::name('okapi-types.')->prefix('/types')->group(function () {
        Route::get('/', [TypeController::class, 'index'])->name('index');
        Route::get('/new', [TypeController::class, 'create'])->name('create');
        Route::post('/', [TypeController::class, 'store'])->name('store');
        Route::get('/{type:slug}/edit', [TypeController::class, 'edit'])->name('edit');
        Route::put('/{type:slug}', [TypeController::class, 'update'])->name('update');
        Route::delete('/{type:slug}', [TypeController::class, 'destroy'])->name('destroy');
    });

    Route::name('okapi-instances.')->prefix('/{type:slug}')->group(function () {
        Route::get('/', [InstanceController::class, 'index'])->name('index');
        Route::get('/new', [InstanceController::class, 'create'])->name('create');
        Route::get('/{instance}', [InstanceController::class, 'show'])->name('show');
        Route::get('/{instance}/edit', [InstanceController::class, 'edit'])->name('edit');
        Route::put('/{instance}', [InstanceController::class, 'update'])->name('update');
        Route::post('/', [InstanceController::class, 'store'])->name('store');
        Route::delete('/{instance}', [InstanceController::class, 'destroy'])->name('destroy');
    });

});

require __DIR__ . '/auth.php';
