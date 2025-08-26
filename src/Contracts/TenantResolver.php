<?php

namespace Litepie\Organization\Contracts;

interface TenantResolver
{
    /**
     * Get the current tenant ID.
     */
    public function getCurrentTenantId(): ?int;

    /**
     * Check if the current request has a tenant context.
     */
    public function hasTenant(): bool;

    /**
     * Set the current tenant ID.
     */
    public function setCurrentTenantId(?int $tenantId): void;

    /**
     * Resolve tenant from the current context.
     */
    public function resolveTenant(): mixed;
}
