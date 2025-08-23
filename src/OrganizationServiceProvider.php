<?php

namespace Litepie\Organization;

use Illuminate\Support\ServiceProvider;
use Litepie\Organization\Services\OrganizationService;

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
            $resolverClass = config('organization.multi_tenant.tenant_resolver');
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
            ], 'migrations');
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
            ], 'config');
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
    }

    /**
     * Register package policies.
     */
    protected function registerPolicies(): void
    {
        if (class_exists(\Illuminate\Foundation\Support\Providers\AuthServiceProvider::class)) {
            $gate = $this->app->make(\Illuminate\Contracts\Auth\Access\Gate::class);
            $gate->policy(\Litepie\Organization\Models\Organization::class, \Litepie\Organization\Policies\OrganizationPolicy::class);
        }
    }
}
