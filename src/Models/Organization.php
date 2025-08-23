<?php

namespace Litepie\Organization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Litepie\Organization\Database\Factories\OrganizationFactory;
use Litepie\Organization\Events\OrganizationCreated;
use Litepie\Organization\Events\OrganizationDeleted;
use Litepie\Organization\Events\OrganizationUpdated;
use Litepie\Tenancy\Traits\BelongsToTenant;

class Organization extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'code',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'manager_id',
        'status',
        'meta',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'created' => OrganizationCreated::class,
        'updated' => OrganizationUpdated::class,
        'deleted' => OrganizationDeleted::class,
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return OrganizationFactory::new();
    }

    /**
     * Get the parent organization.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * Get the child organizations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    /**
     * Get the primary manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo($this->getUserModel(), 'manager_id');
    }

    /**
     * Get all assigned users.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany($this->getUserModel(), 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user who created this organization.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo($this->getUserModel(), 'created_by');
    }

    /**
     * Get the user who last updated this organization.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo($this->getUserModel(), 'updated_by');
    }

    /**
     * Scope to filter by organization type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get all descendants of this organization.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors of this organization.
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get the root organization.
     */
    public function root()
    {
        $root = $this;
        while ($root->parent) {
            $root = $root->parent;
        }
        return $root;
    }

    /**
     * Check if this organization is a root organization.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this organization is a leaf (has no children).
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get organization tree.
     */
    public static function tree($parentId = null)
    {
        return static::with(['children.children.children.children'])
            ->where('parent_id', $parentId)
            ->get();
    }

    /**
     * Get all managers (primary + pivot table managers).
     */
    public function managers()
    {
        $managers = collect();

        // Add primary manager
        if ($this->manager) {
            $managers->push($this->manager);
        }

        // Add pivot table managers
        $pivotManagers = $this->users()
            ->wherePivot('role', 'manager')
            ->get();

        return $managers->merge($pivotManagers)->unique('id');
    }

    /**
     * Get the configuration model class.
     */
    protected function getUserModel(): string
    {
        return config('organization.user_model', 'App\Models\User');
    }
}
