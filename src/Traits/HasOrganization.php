<?php

namespace Litepie\Organization\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Litepie\Organization\Models\Organization;

trait HasOrganization
{
    /**
     * Get all organizations associated with this user.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get organizations where this user is the primary manager.
     */
    public function managedOrganizations()
    {
        return $this->hasMany(Organization::class, 'manager_id');
    }

    /**
     * Get organizations where this user has a specific role.
     */
    public function organizationsWithRole(string $role)
    {
        return $this->organizations()->wherePivot('role', $role);
    }

    /**
     * Check if user belongs to a specific organization.
     */
    public function belongsToOrganization($organizationId): bool
    {
        return $this->organizations()->where('organization_id', $organizationId)->exists();
    }

    /**
     * Check if user has a specific role in an organization.
     */
    public function hasRoleInOrganization($organizationId, string $role): bool
    {
        return $this->organizations()
            ->where('organization_id', $organizationId)
            ->wherePivot('role', $role)
            ->exists();
    }

    /**
     * Check if user is a primary manager of an organization.
     */
    public function isPrimaryManagerOf($organizationId): bool
    {
        return $this->managedOrganizations()->where('id', $organizationId)->exists();
    }

    /**
     * Get all roles for user in a specific organization.
     */
    public function getRolesInOrganization($organizationId)
    {
        return $this->organizations()
            ->where('organization_id', $organizationId)
            ->pluck('role');
    }

    /**
     * Assign user to an organization with a role.
     */
    public function assignToOrganization($organizationId, string $role = 'member')
    {
        return $this->organizations()->syncWithoutDetaching([
            $organizationId => ['role' => $role]
        ]);
    }

    /**
     * Remove user from an organization.
     */
    public function removeFromOrganization($organizationId, string $role = null)
    {
        if ($role) {
            return $this->organizations()
                ->wherePivot('role', $role)
                ->detach($organizationId);
        }

        return $this->organizations()->detach($organizationId);
    }

    /**
     * Get all organizations where user has management roles.
     */
    public function getManagementOrganizations()
    {
        $primaryManaged = $this->managedOrganizations()->get();
        $secondaryManaged = $this->organizationsWithRole('manager')->get();

        return $primaryManaged->merge($secondaryManaged)->unique('id');
    }
}
