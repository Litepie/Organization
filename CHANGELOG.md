# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of the Organization package
- Single table organization hierarchy support
- Organization types: company, branch, department, division, sub_division
- Manager assignment with primary and secondary managers
- User role assignments within organizations
- Recursive tree operations
- Policy-based authorization
- Event-driven architecture (OrganizationCreated, OrganizationUpdated, OrganizationDeleted, ManagerAssigned, ManagerRemoved)
- Comprehensive API and web controllers
- Factory and seeder for testing and development
- PHPUnit test coverage
- HasOrganization trait for User model
- OrganizationService for business logic
- Form request validation
- Blade view templates
- Configuration file for customization

### Features
- RESTful API endpoints for CRUD operations
- Organization tree view
- Search and filter functionality
- Bulk user assignment
- Breadcrumb generation
- Statistics and reporting
- Validation for circular references
- Database migrations for organizations and organization_user tables

## [1.0.0] - 2025-08-22

### Added
- Initial package release
