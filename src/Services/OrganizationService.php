<?php

namespace Litepie\Organization\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Litepie\Organization\Events\ManagerAssigned;
use Litepie\Organization\Events\ManagerRemoved;
use Litepie\Organization\Models\Organization;
use Litepie\Tenancy\Facades\Tenancy;

class OrganizationService
{
    /**
     * Create a new organization.
     */
    public function create(array $data): Organization
    {
        return DB::transaction(function () use ($data) {
            $organization = Organization::create($data);

            // Assign creator as primary manager if no manager specified
            if (!isset($data['manager_id']) && auth()->check()) {
                $organization->update(['manager_id' => auth()->id()]);
            }

            return $organization->load(['parent', 'manager', 'creator']);
        });
    }

    /**
     * Update an organization.
     */
    public function update(Organization $organization, array $data): Organization
    {
        return DB::transaction(function () use ($organization, $data) {
            $oldManagerId = $organization->manager_id;
            
            $organization->update($data);

            // Fire manager assignment event if manager changed
            if (isset($data['manager_id']) && $oldManagerId !== $data['manager_id']) {
                if ($data['manager_id']) {
                    $userModel = config('organization.user_model');
                    $newManager = $userModel::find($data['manager_id']);
                    event(new ManagerAssigned($organization, $newManager, 'primary'));
                }
            }

            return $organization->fresh(['parent', 'manager', 'creator']);
        });
    }

    /**
     * Delete an organization and handle children.
     */
    public function delete(Organization $organization, bool $moveChildrenToParent = true): bool
    {
        return DB::transaction(function () use ($organization, $moveChildrenToParent) {
            if ($moveChildrenToParent && $organization->children()->exists()) {
                // Move children to this organization's parent
                $organization->children()->update([
                    'parent_id' => $organization->parent_id
                ]);
            }

            return $organization->delete();
        });
    }

    /**
     * Assign a user to an organization with a specific role.
     */
    public function assignUser(Organization $organization, $userId, string $role = 'member'): bool
    {
        $userModel = config('organization.user_model');
        $user = $userModel::find($userId);

        if (!$user) {
            return false;
        }

        $organization->users()->syncWithoutDetaching([
            $userId => ['role' => $role]
        ]);

        event(new ManagerAssigned($organization, $user, $role));

        return true;
    }

    /**
     * Remove a user from an organization.
     */
    public function removeUser(Organization $organization, $userId, string $role = null): bool
    {
        $userModel = config('organization.user_model');
        $user = $userModel::find($userId);

        if (!$user) {
            return false;
        }

        if ($role) {
            $detached = $organization->users()
                ->wherePivot('role', $role)
                ->detach($userId);
        } else {
            $detached = $organization->users()->detach($userId);
        }

        if ($detached) {
            event(new ManagerRemoved($organization, $user, $role ?? 'all'));
        }

        return (bool) $detached;
    }

    /**
     * Get organization hierarchy tree.
     */
    public function getTree(int $parentId = null): Collection
    {
        $query = Organization::with(['children' => function ($query) {
                $query->orderBy('name');
            }])
            ->when($parentId, function ($query, $parentId) {
                return $query->where('parent_id', $parentId);
            }, function ($query) {
                return $query->whereNull('parent_id');
            })
            ->orderBy('name');

        // Apply tenant scope if multi-tenancy is enabled
        if ($this->tenantResolver->isEnabled() && $this->tenantResolver->getCurrentTenantId()) {
            $query->forCurrentTenant();
        }

        return $query->get();
    }

    /**
     * Move an organization to a new parent.
     */
    public function moveOrganization(Organization $organization, int $newParentId = null): bool
    {
        // Prevent circular references
        if ($newParentId && $this->wouldCreateCircularReference($organization, $newParentId)) {
            return false;
        }

        return $organization->update(['parent_id' => $newParentId]);
    }

    /**
     * Check if moving an organization would create a circular reference.
     */
    protected function wouldCreateCircularReference(Organization $organization, int $newParentId): bool
    {
        $newParent = Organization::find($newParentId);
        
        if (!$newParent) {
            return false;
        }

        // Check if the new parent is a descendant of the organization
        return $organization->isAncestorOf($newParent);
    }

    /**
     * Get organization statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Organization::count(),
            'by_type' => Organization::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_status' => Organization::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'root_organizations' => Organization::whereNull('parent_id')->count(),
            'organizations_with_managers' => Organization::whereNotNull('manager_id')->count(),
        ];
    }

    /**
     * Search organizations by name or code.
     */
    public function search(string $query, array $filters = []): Collection
    {
        return Organization::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->when(isset($filters['type']), function ($q) use ($filters) {
                return $q->where('type', $filters['type']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['parent_id']), function ($q) use ($filters) {
                return $q->where('parent_id', $filters['parent_id']);
            })
            ->with(['parent', 'manager'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Bulk assign users to an organization.
     */
    public function bulkAssignUsers(Organization $organization, array $userRoles): array
    {
        $results = [];

        DB::transaction(function () use ($organization, $userRoles, &$results) {
            foreach ($userRoles as $userId => $role) {
                $results[$userId] = $this->assignUser($organization, $userId, $role);
            }
        });

        return $results;
    }

    /**
     * Get organization path (breadcrumb).
     */
    public function getOrganizationPath(Organization $organization): array
    {
        $path = [];
        $current = $organization;

        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'name' => $current->name,
                'type' => $current->type,
            ]);
            $current = $current->parent;
        }

        return $path;
    }
}
