<?php

namespace Litepie\Organization;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Litepie\Organization\Services\OrganizationService;
use Litepie\Organization\Models\Organization;
use Litepie\Organization\Policies\OrganizationPolicy;

class OrganizationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
        $this->publishConfig();
        $this->loadRoutes();
        $this->loadViews();
        $this->registerPolicies();
        $this->publishAssets();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/organization.php', 'organization'
        );

        // Register tenant resolver
        $this->app->singleton(\Litepie\Organization\Contracts\TenantResolver::class, function ($app) {
            $resolverClass = config('organization.tenancy.tenant_resolver');
            if ($resolverClass && class_exists($resolverClass)) {
                return new $resolverClass;
            }
            return new \Litepie\Organization\Services\DefaultTenantResolver;
        });

        $this->app->singleton(OrganizationService::class, function ($app) {
            return new OrganizationService(
                $app->make(\Litepie\Organization\Contracts\TenantResolver::class)
            );
        });
    }

    /**
     * Publish migration files.
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'organization-migrations');
        }
    }

    /**
     * Publish configuration file.
     */
    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/organization.php' => config_path('organization.php'),
            ], 'organization-config');
        }
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    /**
     * Load package views.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'organization');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/organization'),
            ], 'organization-views');
        }
    }

    /**
     * Register package policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Organization::class, OrganizationPolicy::class);
    }

    /**
     * Publish package assets.
     */
    protected function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/organization'),
            ], 'organization-assets');
        }
    }
}
