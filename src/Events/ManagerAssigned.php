<?php

namespace Litepie\Organization\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Litepie\Organization\Models\Organization;

class ManagerAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * The organization instance.
     */
    public Organization $organization;

    /**
     * The user assigned as manager.
     */
    public $user;

    /**
     * The role assigned.
     */
    public string $role;

    /**
     * Create a new event instance.
     */
    public function __construct(Organization $organization, $user, string $role = 'manager')
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->role = $role;
    }
}
