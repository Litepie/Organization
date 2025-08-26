# Contributing to Litepie Organization

Thank you for considering contributing to Litepie Organization! This guide will help you get started with contributing to our Laravel 12 compatible package.

## Development Environment

### Requirements

- **PHP 8.2+**
- **Laravel 12.0+**
- **Composer 2.5+**
- **Node.js 18+ (for asset compilation)**
- **MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+**

### Setup

1. **Fork and Clone**
   ```bash
   git clone https://github.com/YOUR_USERNAME/organization.git
   cd organization
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install # if you're working on frontend assets
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

## How to Contribute

### 1. Fork and Branch
- Fork the repository and create your branch from `master`
- Use descriptive branch names: `feature/add-new-organization-type` or `fix/tenant-scoping-issue`

### 2. Development Guidelines

#### Code Style
- Follow **PSR-12** coding standards
- Use **PHP 8.2+** features where appropriate (typed properties, readonly classes, etc.)
- Leverage **Laravel 12** features and best practices
- Write **type-safe** code with proper type hints

#### Example of modern PHP/Laravel code:
```php
// Good - Modern Laravel 12 style
protected function casts(): array
{
    return [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}

// Good - PHP 8.2 features
public function __construct(
    private readonly OrganizationService $service,
    private readonly TenantResolver $tenantResolver
) {}
```

### 3. Testing

#### Running Tests
```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/Feature/OrganizationTest.php

# Run with detailed output
vendor/bin/phpunit --verbose
```

#### Writing Tests
- Add tests for **all new features**
- Ensure **bug fixes** include regression tests
- Use **Feature tests** for end-to-end functionality
- Use **Unit tests** for isolated components
- Test **multi-tenant scenarios** when applicable

Example test structure:
```php
class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_organization_with_tenant_scoping(): void
    {
        $tenant = $this->createTenant();
        $this->actingAsTenant($tenant);
        
        $organization = Organization::factory()->create([
            'name' => 'Test Organization'
        ]);
        
        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'tenant_id' => $tenant->id
        ]);
    }
}
```

### 4. Documentation
- Update **README.md** for new features
- Add **docblocks** to all public methods
- Update **CHANGELOG.md** following [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
- Include **upgrade instructions** for breaking changes

### 5. Commit Guidelines

#### Commit Message Format
```
type(scope): description

[optional body]

[optional footer]
```

#### Types
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting, etc.)
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

#### Examples
```bash
feat(tenancy): add support for subdomain-based tenant resolution
fix(organization): resolve cascade deletion issue with managers
docs(readme): update Laravel 12 installation instructions
test(unit): add comprehensive tests for organization hierarchy
```

## Reporting Issues

### Before Reporting
- Search **existing issues** to avoid duplicates
- Check if the issue exists in the **latest version**
- Verify the issue with **minimal reproduction code**

### Issue Template
```markdown
**Environment:**
- PHP Version: 8.2.x
- Laravel Version: 12.x
- Package Version: 2.x
- Database: MySQL 8.0

**Description:**
Clear description of the issue

**Steps to Reproduce:**
1. Step one
2. Step two
3. Expected vs actual behavior

**Code Example:**
```php
// Minimal code that reproduces the issue
```

**Additional Context:**
Any relevant logs, screenshots, or configuration details
```

## Pull Request Process

### 1. Before Submitting
- [ ] All tests pass (`composer test`)
- [ ] Code follows PSR-12 standards (`composer lint`)
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] No merge conflicts with master

### 2. PR Description Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Added/updated tests
- [ ] All tests pass
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Breaking changes documented
```

### 3. Review Process
- **Maintainer review** required before merge
- **CI/CD checks** must pass
- **Discussion and feedback** welcome
- **Approval** from core team member required

## Development Workflow

### 1. Local Development
```bash
# Start development server
php artisan serve

# Watch for file changes (if applicable)
npm run dev

# Run tests in watch mode
vendor/bin/phpunit --testdox
```

### 2. Database Debugging
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Check database schema
php artisan schema:dump

# Debug queries
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());
```

### 3. Multi-Tenancy Testing
```bash
# Test with tenancy enabled
ORGANIZATION_TENANCY_ENABLED=true php artisan test

# Test tenant switching
$tenant1 = Tenant::first();
$tenant2 = Tenant::skip(1)->first();

$tenant1->execute(function() {
    // Operations scoped to tenant1
});
```

## Code Quality Tools

### Static Analysis
```bash
# PHPStan analysis
vendor/bin/phpstan analyse

# Psalm analysis  
vendor/bin/psalm

# PHP CS Fixer
vendor/bin/php-cs-fixer fix
```

### Performance Testing
```bash
# Benchmark critical operations
php artisan tinker
Benchmark::dd([
    'Tree Query' => fn() => Organization::tree(),
    'Manager Query' => fn() => $org->managers(),
]);
```

## Community Guidelines

### Code of Conduct
- Be **respectful and inclusive**
- **Constructive feedback** only
- **Help others** learn and grow
- Follow our [Code of Conduct](CODE_OF_CONDUCT.md)

### Communication
- **GitHub Issues** for bug reports and feature requests
- **GitHub Discussions** for general questions
- **Pull Request comments** for code review discussions

### Recognition
Contributors will be:
- **Listed in CONTRIBUTORS.md**
- **Mentioned in release notes**
- **Invited to maintainer team** (for significant contributions)

## Release Process

### Versioning
We follow [Semantic Versioning](https://semver.org/):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist
- [ ] Version bumped in `composer.json`
- [ ] CHANGELOG.md updated
- [ ] Documentation updated
- [ ] Tests passing
- [ ] Tagged release created
- [ ] Packagist notified

## Getting Help

### Resources
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Package Documentation](README.md)
- [Upgrade Guide](UPGRADE.md)

### Support Channels
- **GitHub Issues** - Bug reports and feature requests
- **GitHub Discussions** - General questions and community support
- **Email** - maintainers@litepie.org (for security issues only)

Thank you for contributing to make Litepie Organization better! 🚀
