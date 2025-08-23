<?php

namespace Litepie\Organization\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Litepie\Organization\Http\Requests\CreateOrganizationRequest;
use Litepie\Organization\Http\Requests\UpdateOrganizationRequest;
use Litepie\Organization\Http\Resources\OrganizationResource;
use Litepie\Organization\Models\Organization;
use Litepie\Organization\Services\OrganizationService;
use Litepie\Tenancy\Facades\Tenancy;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected OrganizationService $organizationService
    ) {
        $this->middleware('auth:api');
        $this->middleware('tenant.required'); // Litepie Tenancy middleware
    }

    /**
     * Display a listing of organizations.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $query = Organization::with(['parent', 'manager', 'creator']);

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $perPage = min($request->get('per_page', 15), config('organization.pagination.max_per_page', 100));
        $organizations = $query->paginate($perPage);

        return response()->json([
            'data' => OrganizationResource::collection($organizations),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
                'tenant_id' => Tenancy::current()?->getTenantId(),
            ]
        ]);
    }

    /**
     * Store a newly created organization.
     */
    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', Organization::class);

        $organization = $this->organizationService->create($request->validated());

        return response()->json([
            'data' => new OrganizationResource($organization),
            'message' => 'Organization created successfully.'
        ], 201);
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $organization->load(['parent', 'children', 'manager', 'users', 'creator']);

        return response()->json([
            'data' => new OrganizationResource($organization)
        ]);
    }

    /**
     * Update the specified organization.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $organization = $this->organizationService->update($organization, $request->validated());

        return response()->json([
            'data' => new OrganizationResource($organization),
            'message' => 'Organization updated successfully.'
        ]);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $this->organizationService->delete($organization);

        return response()->json([
            'message' => 'Organization deleted successfully.'
        ]);
    }

    /**
     * Get organization tree structure.
     */
    public function tree(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $parentId = $request->get('parent_id');
        $tree = $this->organizationService->getTree($parentId);

        return response()->json([
            'data' => OrganizationResource::collection($tree)
        ]);
    }

    /**
     * Assign a user to an organization.
     */
    public function assignUser(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('assignManagers', $organization);

        $request->validate([
            'user_id' => 'required|integer|exists:' . $this->getUserTable() . ',id',
            'role' => 'required|string|in:' . implode(',', array_keys(config('organization.manager_roles', [])))
        ]);

        $success = $this->organizationService->assignUser(
            $organization,
            $request->user_id,
            $request->role
        );

        if ($success) {
            return response()->json([
                'message' => 'User assigned successfully.'
            ]);
        }

        return response()->json([
            'message' => 'Failed to assign user.'
        ], 400);
    }

    /**
     * Remove a user from an organization.
     */
    public function removeUser(Request $request, Organization $organization, int $userId): JsonResponse
    {
        $this->authorize('removeManagers', $organization);

        $success = $this->organizationService->removeUser(
            $organization,
            $userId,
            $request->get('role')
        );

        if ($success) {
            return response()->json([
                'message' => 'User removed successfully.'
            ]);
        }

        return response()->json([
            'message' => 'Failed to remove user.'
        ], 400);
    }

    /**
     * Get organization statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $statistics = $this->organizationService->getStatistics();

        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Search organizations.
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'parent_id' => 'nullable|integer'
        ]);

        $results = $this->organizationService->search(
            $request->query,
            $request->only(['type', 'status', 'parent_id'])
        );

        return response()->json([
            'data' => OrganizationResource::collection($results)
        ]);
    }

    /**
     * Get current tenant information.
     */
    public function tenantInfo(): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $tenant = Tenancy::current();

        return response()->json([
            'data' => [
                'tenant_id' => $tenant?->getTenantId(),
                'tenant_name' => $tenant?->getConfig('name'),
                'tenant_domain' => $tenant?->getDomain(),
                'organizations_count' => Organization::count(),
            ]
        ]);
    }

    /**
     * Get the user table name from config.
     */
    protected function getUserTable(): string
    {
        $userModel = config('organization.user_model', 'App\Models\User');
        return (new $userModel)->getTable();
    }
}
