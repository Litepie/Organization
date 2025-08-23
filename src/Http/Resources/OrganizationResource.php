<?php

namespace Litepie\Organization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'type_label' => config('organization.types')[$this->type] ?? $this->type,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => config('organization.statuses')[$this->status] ?? $this->status,
            'depth' => $this->depth,
            'full_path' => $this->full_path,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships
            'parent' => $this->whenLoaded('parent', function () {
                return new self($this->parent);
            }),
            'children' => $this->whenLoaded('children', function () {
                return self::collection($this->children);
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                    'email' => $this->manager->email,
                ];
            }),
            'users' => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->pivot->role,
                        'assigned_at' => $user->pivot->created_at?->toISOString(),
                    ];
                });
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ] : null;
            }),
            
            // Additional metadata
            'has_children' => $this->children()->exists(),
            'children_count' => $this->children()->count(),
            'users_count' => $this->users()->count(),
            
            // Permissions for current user
            'can' => [
                'view' => $this->when(auth()->check(), function () {
                    return auth()->user()->can('view', $this->resource);
                }),
                'update' => $this->when(auth()->check(), function () {
                    return auth()->user()->can('update', $this->resource);
                }),
                'delete' => $this->when(auth()->check(), function () {
                    return auth()->user()->can('delete', $this->resource);
                }),
                'assign_managers' => $this->when(auth()->check(), function () {
                    return auth()->user()->can('assignManagers', $this->resource);
                }),
            ],
        ];
    }
}
