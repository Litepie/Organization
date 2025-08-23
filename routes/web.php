<?php

use Illuminate\Support\Facades\Route;
use Litepie\Organization\Http\Controllers\Web\OrganizationController;

/*
|--------------------------------------------------------------------------
| Organization Web Routes
|--------------------------------------------------------------------------
|
| Here are the web routes for the Organization package.
|
*/

Route::prefix(config('organization.routes.web.prefix', 'organizations'))
    ->middleware(config('organization.routes.web.middleware', ['web', 'auth']))
    ->name('organizations.')
    ->group(function () {
        
        // Organization web routes
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::get('/create', [OrganizationController::class, 'create'])->name('create');
        Route::post('/', [OrganizationController::class, 'store'])->name('store');
        Route::get('/{organization}', [OrganizationController::class, 'show'])->name('show');
        Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
        Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
        Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
        
        // Tree view
        Route::get('/tree/view', [OrganizationController::class, 'tree'])->name('tree');
        
        // Manager assignment
        Route::get('/{organization}/managers', [OrganizationController::class, 'managers'])->name('managers');
        Route::post('/{organization}/managers', [OrganizationController::class, 'assignManager'])->name('assign-manager');
        Route::delete('/{organization}/managers/{user}', [OrganizationController::class, 'removeManager'])->name('remove-manager');
    });
