<?php

namespace Litepie\Organization\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Litepie\Organization\Models\Organization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any organizations.
     */
    public function viewAny($user): bool
    {
        return $user->can('organization.view') ?? true;
    }

    /**
     * Determine whether the user can view the organization.
     */
    public function view($user, Organization $organization): bool
    {
        // User can view if they have general permission or belong to the organization
        return $user->can('organization.view') || 
               $user->belongsToOrganization($organization->id) ||
               $user->isPrimaryManagerOf($organization->id);
    }

    /**
     * Determine whether the user can create organizations.
     */
    public function create($user): bool
    {
        return $user->can('organization.create') ?? true;
    }

    /**
     * Determine whether the user can update the organization.
     */
    public function update($user, Organization $organization): bool
    {
        return $user->can('organization.update') ||
               $user->isPrimaryManagerOf($organization->id) ||
               $user->hasRoleInOrganization($organization->id, 'manager');
    }

    /**
     * Determine whether the user can delete the organization.
     */
    public function delete($user, Organization $organization): bool
    {
        return $user->can('organization.delete') ||
               $user->isPrimaryManagerOf($organization->id);
    }

    /**
     * Determine whether the user can assign managers to the organization.
     */
    public function assignManagers($user, Organization $organization): bool
    {
        return $user->can('organization.assign_managers') ||
               $user->isPrimaryManagerOf($organization->id) ||
               $user->hasRoleInOrganization($organization->id, 'manager');
    }

    /**
     * Determine whether the user can remove managers from the organization.
     */
    public function removeManagers($user, Organization $organization): bool
    {
        return $user->can('organization.assign_managers') ||
               $user->isPrimaryManagerOf($organization->id);
    }

    /**
     * Determine whether the user can view organization members.
     */
    public function viewMembers($user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization->id) ||
               $user->isPrimaryManagerOf($organization->id) ||
               $user->hasRoleInOrganization($organization->id, 'manager');
    }

    /**
     * Determine whether the user can manage organization hierarchy.
     */
    public function manageHierarchy($user, Organization $organization): bool
    {
        return $user->can('organization.update') ||
               $user->isPrimaryManagerOf($organization->id);
    }
}
