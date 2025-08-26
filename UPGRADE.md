# Upgrade Guide

This guide will help you upgrade the Litepie Organization package to support Laravel 12.

## From Laravel 10/11 to Laravel 12

### Requirements

Before upgrading, ensure your system meets the following requirements:

- **PHP 8.2+** (was PHP 8.1+)
- **Laravel 12.0+** (was Laravel 10.0+/11.0+)
- **Composer 2.5+**

### Step 1: Update Composer Dependencies

Update your `composer.json` file to require the Laravel 12 compatible version:

```json
{
    "require": {
        "litepie/organization": "^2.0",
        "litepie/tenancy": "^2.0"
    }
}
```

Then run:

```bash
composer update litepie/organization litepie/tenancy
```

### Step 2: Update Configuration

If you have published the configuration file, review and update `config/organization.php`:

```php
// Add this new configuration option
'tenancy' => [
    'enabled' => env('ORGANIZATION_TENANCY_ENABLED', false),
    'tenant_column' => 'tenant_id',
    'tenant_resolver' => \Litepie\Organization\Services\DefaultTenantResolver::class, // New
],
```

### Step 3: Database Migrations

If you've made custom modifications to the database schema, you may need to update your migrations to use Laravel 12's improved migration features:

#### Before (Laravel 10/11):
```php
$table->unsignedBigInteger('parent_id')->nullable();
$table->foreign('parent_id')->references('id')->on('organizations')->onDelete('cascade');
```

#### After (Laravel 12):
```php
$table->foreignId('parent_id')->nullable()->constrained('organizations')->onDelete('cascade');
```

### Step 4: Model Updates

If you've extended the Organization model, update your casts to use the new function-based syntax:

#### Before:
```php
protected $casts = [
    'meta' => 'array',
    'created_at' => 'datetime',
];
```

#### After:
```php
protected function casts(): array
{
    return [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}
```

### Step 5: Service Provider Updates

If you've extended the OrganizationServiceProvider, note these changes:

- Policy registration now uses `Gate::policy()` directly
- New asset publishing methods with granular tags
- Enhanced view publishing with vendor directory structure

### Step 6: Test Configuration

Update your PHPUnit configuration to use PHPUnit 11 schema:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         cacheDirectory=".phpunit.cache"
>
    <!-- Your test configuration -->
</phpunit>
```

### Step 7: Clear Caches

After upgrading, clear all application caches:

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

### Step 8: Run Tests

Ensure all tests pass with the new version:

```bash
composer test
```

## Breaking Changes

### 1. PHP Version Requirement
- **Minimum PHP version increased from 8.1 to 8.2**
- Update your production environment accordingly

### 2. Tenancy Configuration
- Configuration key changed from `multi_tenant` to `tenancy`
- Added `tenant_resolver` configuration option

### 3. Casts Method
- `$casts` property replaced with `casts()` method for better type safety
- Update any extended models accordingly

### 4. Dependencies
- Orchestra Testbench updated to 10.x
- PHPUnit updated to 11.x
- Litepie Tenancy updated to 2.x

## New Features

### 1. Enhanced Type Safety
- Better type hints throughout the codebase
- Improved method return types
- Enhanced null safety

### 2. Improved Database Migrations
- Modern foreign key constraint syntax
- Better index organization
- Enhanced column definitions

### 3. Better Testing Infrastructure
- PHPUnit 11 compatibility
- Enhanced test configuration
- Improved test utilities

## Troubleshooting

### Common Issues

#### Composer Dependency Conflicts
```bash
composer update --with-all-dependencies litepie/organization
```

#### Database Migration Issues
If you encounter migration issues, you may need to refresh your database:

```bash
php artisan migrate:fresh
php artisan db:seed
```

#### Configuration Cache Issues
Clear configuration cache if you experience unexpected behavior:

```bash
php artisan config:clear
php artisan config:cache
```

#### Tenancy Integration Issues
If using multi-tenancy, ensure the tenancy package is properly configured:

```bash
php artisan vendor:publish --provider="Litepie\Tenancy\TenancyServiceProvider"
```

### Getting Help

If you encounter issues during the upgrade:

1. Check the [GitHub Issues](https://github.com/litepie/organization/issues)
2. Review the [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade)
3. Create a new issue with detailed information about your problem

## Post-Upgrade Checklist

- [ ] All dependencies updated successfully
- [ ] Configuration files reviewed and updated
- [ ] Database migrations run without errors
- [ ] All tests pass
- [ ] Application functionality verified
- [ ] Production environment requirements met
- [ ] Team notified of upgrade completion

## Performance Improvements

Laravel 12 brings several performance improvements that benefit this package:

- **Better query optimization** - Enhanced Eloquent performance
- **Improved caching** - Better config and route caching
- **Memory efficiency** - Reduced memory footprint
- **Faster boots** - Improved service provider registration

Take advantage of these improvements by ensuring your application is properly optimized for Laravel 12.
