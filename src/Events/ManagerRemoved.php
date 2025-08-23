<?php

namespace Litepie\Organization\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Litepie\Organization\Models\Organization;

class ManagerRemoved
{
    use Dispatchable, SerializesModels;

    /**
     * The organization instance.
     */
    public Organization $organization;

    /**
     * The user removed as manager.
     */
    public $user;

    /**
     * The role that was removed.
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
