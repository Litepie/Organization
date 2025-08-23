@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Organizations</h4>
                    @can('create', \Litepie\Organization\Models\Organization::class)
                        <a href="{{ route('organizations.create') }}" class="btn btn-primary">
                            Create Organization
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search organizations..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach(config('organization.types', []) as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    @foreach(config('organization.statuses', []) as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Organizations Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Parent</th>
                                    <th>Manager</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $organization)
                                    <tr>
                                        <td>
                                            <a href="{{ route('organizations.show', $organization) }}">
                                                {{ $organization->name }}
                                            </a>
                                        </td>
                                        <td>{{ $organization->code }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ config('organization.types')[$organization->type] ?? $organization->type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($organization->parent)
                                                <a href="{{ route('organizations.show', $organization->parent) }}">
                                                    {{ $organization->parent->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Root</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($organization->manager)
                                                {{ $organization->manager->name }}
                                            @else
                                                <span class="text-muted">No manager</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $organization->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ config('organization.statuses')[$organization->status] ?? $organization->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $organization)
                                                    <a href="{{ route('organizations.show', $organization) }}" 
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                @endcan
                                                
                                                @can('update', $organization)
                                                    <a href="{{ route('organizations.edit', $organization) }}" 
                                                       class="btn btn-sm btn-outline-secondary">Edit</a>
                                                @endcan
                                                
                                                @can('delete', $organization)
                                                    <form method="POST" action="{{ route('organizations.destroy', $organization) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this organization?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No organizations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $organizations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
