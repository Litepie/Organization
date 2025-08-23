<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Organization Types
    |--------------------------------------------------------------------------
    |
    | Define the available organization types in your application.
    |
    */
    'types' => [
        'company' => 'Company',
        'branch' => 'Branch',
        'department' => 'Department',
        'division' => 'Division',
        'sub_division' => 'Sub Division',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization Status
    |--------------------------------------------------------------------------
    |
    | Define the available status options for organizations.
    |
    */
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Define the user model class to be used for relationships.
    |
    */
    'user_model' => 'App\Models\User',

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure integration with Litepie\Tenancy package.
    | When enabled, organizations will automatically be scoped to tenants.
    |
    */
    'tenancy' => [
        'enabled' => env('ORGANIZATION_TENANCY_ENABLED', false),
        'tenant_column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure route prefixes and middleware for the package routes.
    |
    */
    'routes' => [
        'api' => [
            'prefix' => 'api',
            'middleware' => ['api'],
        ],
        'web' => [
            'prefix' => 'organizations',
            'middleware' => ['web', 'auth'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for organization listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define permission mappings for organization operations.
    |
    */
    'permissions' => [
        'create' => 'organization.create',
        'view' => 'organization.view',
        'update' => 'organization.update',
        'delete' => 'organization.delete',
        'assign_managers' => 'organization.assign_managers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Manager Roles
    |--------------------------------------------------------------------------
    |
    | Define the available roles for organization managers.
    |
    */
    'manager_roles' => [
        'manager' => 'Manager',
        'supervisor' => 'Supervisor',
        'coordinator' => 'Coordinator',
        'assistant' => 'Assistant',
    ],
];
