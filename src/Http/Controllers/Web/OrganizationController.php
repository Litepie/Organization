<?php

namespace Litepie\Organization\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Litepie\Organization\Http\Requests\CreateOrganizationRequest;
use Litepie\Organization\Http\Requests\UpdateOrganizationRequest;
use Litepie\Organization\Models\Organization;
use Litepie\Organization\Services\OrganizationService;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected OrganizationService $organizationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of organizations.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Organization::class);

        $query = Organization::with(['parent', 'manager']);

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $organizations = $query->paginate(15);

        return view('organization::organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create(): View
    {
        $this->authorize('create', Organization::class);

        $types = config('organization.types', []);
        $statuses = config('organization.statuses', []);
        $organizations = Organization::whereNull('parent_id')->get();

        return view('organization::organizations.create', compact('types', 'statuses', 'organizations'));
    }

    /**
     * Store a newly created organization.
     */
    public function store(CreateOrganizationRequest $request)
    {
        $this->authorize('create', Organization::class);

        $organization = $this->organizationService->create($request->validated());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organization created successfully.');
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['parent', 'children', 'manager', 'users']);

        return view('organization::organizations.show', compact('organization'));
    }

    /**
     * Show the form for editing the specified organization.
     */
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $types = config('organization.types', []);
        $statuses = config('organization.statuses', []);
        $organizations = Organization::where('id', '!=', $organization->id)
            ->whereNull('parent_id')
            ->get();

        return view('organization::organizations.edit', compact('organization', 'types', 'statuses', 'organizations'));
    }

    /**
     * Update the specified organization.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $organization = $this->organizationService->update($organization, $request->validated());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        $this->organizationService->delete($organization);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }

    /**
     * Display the organization tree.
     */
    public function tree(): View
    {
        $this->authorize('viewAny', Organization::class);

        $tree = $this->organizationService->getTree();

        return view('organization::organizations.tree', compact('tree'));
    }

    /**
     * Show organization managers.
     */
    public function managers(Organization $organization): View
    {
        $this->authorize('viewMembers', $organization);

        $organization->load(['manager', 'users']);

        return view('organization::organizations.managers', compact('organization'));
    }

    /**
     * Assign a manager to the organization.
     */
    public function assignManager(Request $request, Organization $organization)
    {
        $this->authorize('assignManagers', $organization);

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string|in:' . implode(',', array_keys(config('organization.manager_roles', [])))
        ]);

        $success = $this->organizationService->assignUser(
            $organization,
            $request->user_id,
            $request->role
        );

        if ($success) {
            return redirect()
                ->route('organizations.managers', $organization)
                ->with('success', 'Manager assigned successfully.');
        }

        return redirect()
            ->back()
            ->with('error', 'Failed to assign manager.');
    }

    /**
     * Remove a manager from the organization.
     */
    public function removeManager(Request $request, Organization $organization, int $userId)
    {
        $this->authorize('removeManagers', $organization);

        $success = $this->organizationService->removeUser(
            $organization,
            $userId,
            $request->get('role')
        );

        if ($success) {
            return redirect()
                ->route('organizations.managers', $organization)
                ->with('success', 'Manager removed successfully.');
        }

        return redirect()
            ->back()
            ->with('error', 'Failed to remove manager.');
    }
}
