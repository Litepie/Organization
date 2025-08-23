<?php

namespace Litepie\Organization\Services;

use Litepie\Organization\Contracts\TenantResolver;

class DefaultTenantResolver implements TenantResolver
{
    /**
     * Get the current tenant ID.
     */
    public function getCurrentTenantId()
    {
        // Try different methods to resolve tenant ID
        
        // 1. Check if tenant is bound in container
        if (app()->bound('current-tenant')) {
            $tenant = app('current-tenant');
            return $tenant ? $tenant->id : null;
        }

        // 2. Check session for tenant ID
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        // 3. Check authenticated user's tenant
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user has getCurrentTenantId method
            if (method_exists($user, 'getCurrentTenantId')) {
                return $user->getCurrentTenantId();
            }
            
            // Check if user has tenant_id attribute
            if (isset($user->tenant_id)) {
                return $user->tenant_id;
            }
        }

        // 4. Check for tenant in request headers (for API)
        if (request()->hasHeader('X-Tenant-ID')) {
            return request()->header('X-Tenant-ID');
        }

        // 5. Check for tenant in request (for web)
        if (request()->has('tenant_id')) {
            return request()->get('tenant_id');
        }

        // 6. Try to resolve from subdomain
        $host = request()->getHost();
        if ($this->isSubdomainTenant($host)) {
            return $this->getTenantFromSubdomain($host);
        }

        return null;
    }

    /**
     * Set the current tenant ID.
     */
    public function setCurrentTenantId($tenantId): void
    {
        // Store in session
        session(['tenant_id' => $tenantId]);

        // Bind to container if tenant model exists
        $tenantModel = config('organization.multi_tenant.tenant_model');
        if ($tenantId && $tenantModel && class_exists($tenantModel)) {
            $tenant = $tenantModel::find($tenantId);
            if ($tenant) {
                app()->singleton('current-tenant', function () use ($tenant) {
                    return $tenant;
                });
            }
        }
    }

    /**
     * Check if multi-tenancy is enabled.
     */
    public function isEnabled(): bool
    {
        return config('organization.multi_tenant.enabled', false);
    }

    /**
     * Check if the host uses subdomain-based tenancy.
     */
    protected function isSubdomainTenant(string $host): bool
    {
        // Simple check - you might want to customize this
        $parts = explode('.', $host);
        return count($parts) >= 3 && $parts[0] !== 'www';
    }

    /**
     * Extract tenant ID from subdomain.
     */
    protected function getTenantFromSubdomain(string $host)
    {
        $subdomain = explode('.', $host)[0];
        
        // You might want to lookup tenant by subdomain
        $tenantModel = config('organization.multi_tenant.tenant_model');
        if ($tenantModel && class_exists($tenantModel)) {
            $tenant = $tenantModel::where('subdomain', $subdomain)->first();
            return $tenant ? $tenant->id : null;
        }

        return $subdomain; // Return subdomain as tenant ID if no model lookup
    }
}
