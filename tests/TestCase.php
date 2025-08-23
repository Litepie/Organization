<?php

namespace Litepie\Organization\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Litepie\Organization\OrganizationServiceProvider;
use Litepie\Tenancy\TenancyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Litepie\\Organization\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TenancyServiceProvider::class,
            OrganizationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/2024_01_01_000001_create_organizations_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2024_01_01_000002_create_organization_user_table.php';
        $migration->up();

        // Create tenants table for testing
        $app['db']->connection()->getSchemaBuilder()->create('tenants', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('subdomain')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Run tenant support migration if tenancy is enabled
        if (config('organization.tenancy.enabled')) {
            $migration = include __DIR__.'/../database/migrations/2025_08_22_000003_add_tenant_support_to_organizations.php';
            $migration->up();
        }
    }

    /**
     * Create a test user.
     */
    protected function createUser(array $attributes = [])
    {
        $userClass = config('organization.user_model', 'App\\Models\\User');
        
        // Create basic user model for testing
        if (!$this->app['db']->connection()->getSchemaBuilder()->hasTable('users')) {
            $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        return $userClass::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }

    /**
     * Create a test tenant.
     */
    protected function createTenant(array $attributes = [])
    {
        $tenantClass = config('tenancy.tenant_model', 'Litepie\\Tenancy\\Models\\Tenant');
        
        return $tenantClass::create(array_merge([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Set the current tenant for testing.
     */
    protected function actingAsTenant($tenant)
    {
        if (class_exists('Litepie\\Tenancy\\Facades\\Tenancy')) {
            \Litepie\Tenancy\Facades\Tenancy::setTenant($tenant);
        }
        
        return $this;
    }
}
