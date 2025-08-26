# Organization Hierarchy Package

[![Build Status](https://github.com/litepie/organization/actions/workflows/tests.yml/badge.svg)](https://github.com/litepie/organization/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)
[![Packagist](https://img.shields.io/packagist/v/litepie/organization.svg)](https://packagist.org/packages/litepie/organization)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/litepie/organization)](https://packagist.org/packages/litepie/organization)

[GitHub Repository](https://github.com/litepie/organization)

A Laravel 12 package for managing organizational hierarchy using a single table structure with support for companies, branches, departments, divisions, and sub-divisions. The package includes built-in multi-tenant support for SaaS applications.

## Requirements

- **PHP 8.2+**
- **Laravel 12.0+**
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

## Features

- ✅ Laravel 12 compatible with modern PHP features
- 🏢 Single table organization hierarchy
- 🔄 Multiple organization types (company, branch, department, division, sub_division)
- 👤 Manager assignment with primary and secondary managers  
- 👥 User role assignments within organizations
- 🌳 Recursive tree operations with optimized queries
- 🔐 Policy-based authorization with Laravel Gates
- 📡 Event-driven architecture with modern event broadcasting
- 🌐 Comprehensive API and web controllers
- 🏗️ **Multi-tenant support with configurable tenant resolution**
- 🎯 **Enhanced type safety with PHP 8.2+ features**

## Installation

Install the package via Composer:

```bash
composer require litepie/organization
```

For multi-tenant applications, also install the Litepie Tenancy package:

```bash
composer require litepie/tenancy
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="Litepie\Organization\OrganizationServiceProvider" --tag="migrations"
php artisan migrate
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Litepie\Organization\OrganizationServiceProvider" --tag="config"
```

## Configuration

The configuration file `config/organization.php` allows you to customize:

- Organization types
- Status options
- User model reference
- Route prefixes
- Middleware settings
- **Multi-tenant integration**

### Multi-Tenant Setup

This package integrates seamlessly with the [Litepie\Tenancy](https://github.com/Litepie/Tenancy) package for multi-tenant applications.

1. **Install Litepie Tenancy**:
```bash
composer require litepie/tenancy
```

2. **Enable tenancy in organization config**:
```php
// config/organization.php
'tenancy' => [
    'enabled' => true,
    'tenant_column' => 'tenant_id',
],
```

3. **Configure tenancy package**:
```bash
php artisan vendor:publish --provider="Litepie\Tenancy\TenancyServiceProvider"
```

4. **Add tenant-aware middleware to routes**:
```php
// In your route files
Route::middleware(['tenant.required'])->group(function () {
    Route::apiResource('organizations', OrganizationController::class);
});
```

#### How Multi-Tenancy Works

When tenancy is enabled:

- Organizations are automatically scoped to the current tenant using the `BelongsToTenant` trait
- All queries are filtered by the current tenant's ID
- New organizations are automatically assigned to the current tenant
- The `tenant_id` column is added to the organizations table

#### Tenant Detection

The package uses Litepie\Tenancy's flexible tenant detection:

```php
// Detect by domain
// tenant1.myapp.com -> tenant1

// Detect by header
// X-Tenant-ID: 123

// Detect by authenticated user
// auth()->user()->tenant_id

// Custom detection in your AppServiceProvider
app()->bind(TenantDetectorContract::class, CustomTenantDetector::class);
```

## Usage

### Basic CRUD Operations

```php
use Litepie\Organization\Models\Organization;

// Create a company
$company = Organization::create([
    'type' => 'company',
    'name' => 'Acme Corporation',
    'code' => 'ACME',
    'status' => 'active',
    'created_by' => auth()->id(),
]);

// Create a branch under the company
$branch = Organization::create([
    'parent_id' => $company->id,
    'type' => 'branch',
    'name' => 'New York Branch',
    'code' => 'ACME-NY',
    'status' => 'active',
    'created_by' => auth()->id(),
]);
```

### Multi-Tenant Operations

When multi-tenancy is enabled, organizations are automatically scoped to the current tenant:

```php
use Litepie\Tenancy\Facades\Tenancy;

// Organizations are automatically filtered by current tenant
$organizations = Organization::all(); // Only current tenant's organizations

// Get current tenant information
$tenant = Tenancy::current();
$tenantId = $tenant?->getTenantId();

// Bypass tenant scoping (admin operations)
$allOrganizations = Organization::withoutTenantScope()->get();

// Manually execute in specific tenant context
$tenant->execute(function () {
    $organizations = Organization::all(); // Scoped to this tenant
});

// Switch tenant context for operations
Tenancy::setTenant($anotherTenant);
$organizations = Organization::all(); // Now scoped to different tenant
```

#### Working with Multiple Tenants

```php
// Get organizations across multiple tenants (admin view)
$crossTenantStats = Organization::withoutTenantScope()
    ->selectRaw('tenant_id, count(*) as total')
    ->groupBy('tenant_id')
    ->get();

// Execute operations for each tenant
foreach (Tenancy::getAllTenants() as $tenant) {
    $tenant->execute(function () use ($tenant) {
        $count = Organization::count();
        echo "Tenant {$tenant->getTenantId()} has {$count} organizations\n";
    });
}
```

### Querying by Type

```php
// Get all companies
$companies = Organization::ofType('company')->get();

// Get all departments
$departments = Organization::ofType('department')->get();

// Get active organizations
$active = Organization::active()->get();
```

### Working with Hierarchy

```php
// Get organization tree
$tree = Organization::tree();

// Get children of an organization
$children = $organization->children;

// Get parent organization
$parent = $organization->parent;

// Get all descendants
$descendants = $organization->descendants();

// Get all ancestors
$ancestors = $organization->ancestors();
```

### Manager Assignment

```php
// Assign primary manager
$organization->update(['manager_id' => $user->id]);

// Assign additional managers with roles
$organization->users()->attach($user->id, ['role' => 'manager']);
$organization->users()->attach($user2->id, ['role' => 'supervisor']);

// Get all managers
$managers = $organization->managers();
```

### User Trait

Add the `HasOrganization` trait to your User model:

```php
use Litepie\Organization\Traits\HasOrganization;

class User extends Authenticatable
{
    use HasOrganization;
}
```

Then use it:

```php
// Get user's organizations
$organizations = $user->organizations;

// Get user's organizations with specific role
$managedOrganizations = $user->organizationsWithRole('manager');

// Check if user belongs to organization
if ($user->belongsToOrganization($organizationId)) {
    // User belongs to this organization
}
```

## API Endpoints

The package provides RESTful API endpoints:

- `GET /api/organizations` - List organizations
- `POST /api/organizations` - Create organization
- `GET /api/organizations/{id}` - Show organization
- `PUT /api/organizations/{id}` - Update organization
- `DELETE /api/organizations/{id}` - Delete organization
- `GET /api/organizations/tree` - Get organization tree
- `POST /api/organizations/{id}/managers` - Assign manager
- `DELETE /api/organizations/{id}/managers/{userId}` - Remove manager

## Events

The package fires the following events:

- `OrganizationCreated`
- `OrganizationUpdated`
- `OrganizationDeleted`
- `ManagerAssigned`
- `ManagerRemoved`

## Testing

Run the tests:

```bash
composer test
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
