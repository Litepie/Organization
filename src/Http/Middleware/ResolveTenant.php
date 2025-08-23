<?php

namespace Litepie\Organization\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Litepie\Organization\Contracts\TenantResolver;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * The tenant resolver instance.
     */
    protected TenantResolver $tenantResolver;

    /**
     * Create a new middleware instance.
     */
    public function __construct(TenantResolver $tenantResolver)
    {
        $this->tenantResolver = $tenantResolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only proceed if multi-tenancy is enabled
        if (!$this->tenantResolver->isEnabled()) {
            return $next($request);
        }

        // Try to resolve tenant from various sources
        $tenantId = $this->resolveTenantFromRequest($request);

        if ($tenantId) {
            $this->tenantResolver->setCurrentTenantId($tenantId);
        }

        return $next($request);
    }

    /**
     * Resolve tenant ID from the request.
     */
    protected function resolveTenantFromRequest(Request $request)
    {
        // 1. Check header (API requests)
        if ($request->hasHeader('X-Tenant-ID')) {
            return $request->header('X-Tenant-ID');
        }

        // 2. Check query parameter
        if ($request->has('tenant_id')) {
            return $request->get('tenant_id');
        }

        // 3. Check route parameter
        if ($request->route('tenant')) {
            return $request->route('tenant');
        }

        // 4. Check subdomain
        $host = $request->getHost();
        if ($this->isSubdomainTenant($host)) {
            return $this->getTenantFromSubdomain($host);
        }

        // 5. Use resolver's default logic
        return $this->tenantResolver->getCurrentTenantId();
    }

    /**
     * Check if the host uses subdomain-based tenancy.
     */
    protected function isSubdomainTenant(string $host): bool
    {
        $parts = explode('.', $host);
        return count($parts) >= 3 && $parts[0] !== 'www';
    }

    /**
     * Extract tenant from subdomain.
     */
    protected function getTenantFromSubdomain(string $host)
    {
        $subdomain = explode('.', $host)[0];
        
        // Look up tenant by subdomain if tenant model is configured
        $tenantModel = config('organization.multi_tenant.tenant_model');
        if ($tenantModel && class_exists($tenantModel)) {
            $tenant = $tenantModel::where('subdomain', $subdomain)->first();
            return $tenant ? $tenant->id : null;
        }

        return $subdomain;
    }
}
