<?php

use App\Http\Controllers\Okapi\InstanceController;
use App\Http\Controllers\Okapi\TypeController;
use Illuminate\Foundation\Application;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::prefix('/okapi')->middleware(['auth', 'verified'])->group(static function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::name('okapi-types.')->prefix('/types')->group(function () {
        Route::get('/', [TypeController::class, 'index'])->name('index');
        Route::get('/new', [TypeController::class, 'create'])->name('create');
        Route::get('/{type:slug}', [TypeController::class, 'show'])->name('show');
        Route::get('/{type:slug}/edit', [TypeController::class, 'edit'])->name('edit');
        Route::put('/{type:slug}', [TypeController::class, 'update'])->name('update');
        Route::post('/', [TypeController::class, 'store'])->name('store');
    });

    Route::name('okapi-instances.')->prefix('/{type:slug}')->group(function () {
        Route::get('/', [InstanceController::class, 'index'])->name('index');
        Route::get('/new', [InstanceController::class, 'create'])->name('create');
        Route::get('/{instance}', [InstanceController::class, 'show'])->name('show');
        Route::get('/{instance}/edit', [InstanceController::class, 'edit'])->name('edit');
        Route::put('/{instance}', [InstanceController::class, 'update'])->name('update');
        Route::post('/', [InstanceController::class, 'store'])->name('store');
    });

});

require __DIR__.'/auth.php';
