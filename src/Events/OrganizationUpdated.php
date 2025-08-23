<?php

namespace Litepie\Organization\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Litepie\Organization\Models\Organization;

class OrganizationUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The organization instance.
     */
    public Organization $organization;

    /**
     * Create a new event instance.
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }
}
