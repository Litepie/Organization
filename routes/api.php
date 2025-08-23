<?php

use Illuminate\Support\Facades\Route;
use Litepie\Organization\Http\Controllers\Api\OrganizationController;

/*
|--------------------------------------------------------------------------
| Organization API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Organization package.
|
*/

Route::prefix(config('organization.routes.api.prefix', 'api'))
    ->middleware(config('organization.routes.api.middleware', ['api']))
    ->group(function () {
        
        // Organization CRUD routes
        Route::apiResource('organizations', OrganizationController::class);
        
        // Additional organization routes
        Route::get('organizations-tree', [OrganizationController::class, 'tree'])
            ->name('organizations.tree');
            
        Route::get('organizations-search', [OrganizationController::class, 'search'])
            ->name('organizations.search');
            
        Route::get('organizations-statistics', [OrganizationController::class, 'statistics'])
            ->name('organizations.statistics');
        
        // User assignment routes
        Route::post('organizations/{organization}/users', [OrganizationController::class, 'assignUser'])
            ->name('organizations.assign-user');
            
        Route::delete('organizations/{organization}/users/{user}', [OrganizationController::class, 'removeUser'])
            ->name('organizations.remove-user');
    });
