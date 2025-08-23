<?php

namespace Litepie\Organization\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Litepie\Organization\Models\Organization;
use Litepie\Organization\Tests\TestCase;

class MultiTenantOrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createUser());
    }

    public function test_organizations_are_scoped_by_tenant()
    {
        // Create organizations for different tenants
        $tenant1 = $this->createTenant(['id' => 1]);
        $tenant2 = $this->createTenant(['id' => 2]);

        $this->setCurrentTenant(1);
        $org1 = Organization::factory()->create(['name' => 'Tenant 1 Org']);

        $this->setCurrentTenant(2);
        $org2 = Organization::factory()->create(['name' => 'Tenant 2 Org']);

        // Switch to tenant 1 and verify only tenant 1 organizations are visible
        $this->setCurrentTenant(1);
        $organizations = Organization::all();
        
        $this->assertCount(1, $organizations);
        $this->assertEquals('Tenant 1 Org', $organizations->first()->name);

        // Switch to tenant 2 and verify only tenant 2 organizations are visible
        $this->setCurrentTenant(2);
        $organizations = Organization::all();
        
        $this->assertCount(1, $organizations);
        $this->assertEquals('Tenant 2 Org', $organizations->first()->name);
    }

    public function test_organization_hierarchy_respects_tenant_boundaries()
    {
        $tenant1 = $this->createTenant(['id' => 1]);
        $tenant2 = $this->createTenant(['id' => 2]);

        // Create hierarchies in different tenants
        $this->setCurrentTenant(1);
        $parent1 = Organization::factory()->company()->create(['name' => 'Parent 1']);
        $child1 = Organization::factory()->branch()->childOf($parent1)->create(['name' => 'Child 1']);

        $this->setCurrentTenant(2);
        $parent2 = Organization::factory()->company()->create(['name' => 'Parent 2']);
        $child2 = Organization::factory()->branch()->childOf($parent2)->create(['name' => 'Child 2']);

        // Verify tree structure for tenant 1
        $this->setCurrentTenant(1);
        $tree = Organization::tree();
        $this->assertCount(1, $tree);
        $this->assertEquals('Parent 1', $tree->first()->name);

        // Verify tree structure for tenant 2
        $this->setCurrentTenant(2);
        $tree = Organization::tree();
        $this->assertCount(1, $tree);
        $this->assertEquals('Parent 2', $tree->first()->name);
    }

    public function test_api_endpoints_respect_tenant_scope()
    {
        $tenant1 = $this->createTenant(['id' => 1]);
        $tenant2 = $this->createTenant(['id' => 2]);

        // Create organizations for different tenants
        $this->setCurrentTenant(1);
        Organization::factory()->create(['name' => 'Tenant 1 Org']);

        $this->setCurrentTenant(2);
        Organization::factory()->create(['name' => 'Tenant 2 Org']);

        // API request for tenant 1
        $response = $this->withHeaders(['X-Tenant-ID' => 1])
            ->getJson('/api/organizations');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Tenant 1 Org', $data[0]['name']);

        // API request for tenant 2
        $response = $this->withHeaders(['X-Tenant-ID' => 2])
            ->getJson('/api/organizations');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Tenant 2 Org', $data[0]['name']);
    }

    public function test_can_create_organization_with_tenant_id()
    {
        $this->setCurrentTenant(1);

        $data = [
            'type' => 'company',
            'name' => 'Multi-Tenant Company',
            'code' => 'MTC',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/organizations', $data);

        $response->assertCreated();
        
        $organization = Organization::first();
        $this->assertEquals(1, $organization->tenant_id);
        $this->assertEquals('Multi-Tenant Company', $organization->name);
    }

    public function test_organization_without_tenant_scope_shows_all()
    {
        $tenant1 = $this->createTenant(['id' => 1]);
        $tenant2 = $this->createTenant(['id' => 2]);

        // Create organizations for different tenants
        $this->setCurrentTenant(1);
        Organization::factory()->create(['name' => 'Tenant 1 Org']);

        $this->setCurrentTenant(2);
        Organization::factory()->create(['name' => 'Tenant 2 Org']);

        // Clear tenant and disable auto-scoping
        $this->clearCurrentTenant();
        
        $organizations = Organization::withoutTenantScope()->get();
        $this->assertCount(2, $organizations);
    }

    public function test_tenant_resolver_works_with_authenticated_user()
    {
        $user = $this->createUser(['tenant_id' => 5]);
        $this->actingAs($user);

        $organization = Organization::factory()->create(['name' => 'User Tenant Org']);

        // Organization should be created with user's tenant_id
        $this->assertEquals(5, $organization->tenant_id);
    }

    public function test_can_switch_between_tenants()
    {
        // Create organizations in different tenants
        $this->setCurrentTenant(1);
        $org1 = Organization::factory()->create(['name' => 'Org 1']);

        $this->setCurrentTenant(2);
        $org2 = Organization::factory()->create(['name' => 'Org 2']);

        $this->setCurrentTenant(3);
        $org3 = Organization::factory()->create(['name' => 'Org 3']);

        // Switch back to tenant 1
        $this->setCurrentTenant(1);
        $this->assertCount(1, Organization::all());
        $this->assertEquals('Org 1', Organization::first()->name);

        // Switch to tenant 2
        $this->setCurrentTenant(2);
        $this->assertCount(1, Organization::all());
        $this->assertEquals('Org 2', Organization::first()->name);

        // Switch to tenant 3
        $this->setCurrentTenant(3);
        $this->assertCount(1, Organization::all());
        $this->assertEquals('Org 3', Organization::first()->name);
    }
}
