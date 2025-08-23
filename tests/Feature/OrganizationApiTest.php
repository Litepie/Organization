<?php

namespace Litepie\Organization\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Litepie\Organization\Models\Organization;
use Litepie\Organization\Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createUser());
    }

    public function test_can_list_organizations()
    {
        Organization::factory()->count(3)->create();

        $response = $this->getJson('/api/organizations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'type',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_create_organization()
    {
        $data = [
            'type' => 'company',
            'name' => 'Test Company',
            'code' => 'TEST',
            'description' => 'A test company',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/organizations', $data);

        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'Test Company',
                'code' => 'TEST'
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Company',
            'code' => 'TEST'
        ]);
    }

    public function test_can_show_organization()
    {
        $organization = Organization::factory()->create();

        $response = $this->getJson("/api/organizations/{$organization->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $organization->id,
                'name' => $organization->name,
                'code' => $organization->code
            ]);
    }

    public function test_can_update_organization()
    {
        $organization = Organization::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/organizations/{$organization->id}", $data);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Name'
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_organization()
    {
        $organization = Organization::factory()->create();

        $response = $this->deleteJson("/api/organizations/{$organization->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('organizations', [
            'id' => $organization->id
        ]);
    }

    public function test_can_get_organization_tree()
    {
        $parent = Organization::factory()->company()->create();
        $child = Organization::factory()->branch()->childOf($parent)->create();

        $response = $this->getJson('/api/organizations-tree');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                        'children'
                    ]
                ]
            ]);
    }

    public function test_can_search_organizations()
    {
        Organization::factory()->create(['name' => 'Searchable Company']);
        Organization::factory()->create(['name' => 'Other Company']);

        $response = $this->getJson('/api/organizations-search?query=Searchable');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => 'Searchable Company'
            ]);
    }

    public function test_can_assign_user_to_organization()
    {
        $organization = Organization::factory()->create();
        $user = $this->createUser();

        $data = [
            'user_id' => $user->id,
            'role' => 'manager'
        ];

        $response = $this->postJson("/api/organizations/{$organization->id}/users", $data);

        $response->assertOk();

        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'manager'
        ]);
    }

    public function test_validation_fails_for_invalid_organization_data()
    {
        $data = [
            'type' => 'invalid_type',
            'name' => '',
            'code' => '',
            'status' => 'invalid_status'
        ];

        $response = $this->postJson('/api/organizations', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'name', 'code', 'status']);
    }

    public function test_cannot_create_organization_with_duplicate_code()
    {
        Organization::factory()->create(['code' => 'DUPLICATE']);

        $data = [
            'type' => 'company',
            'name' => 'Test Company',
            'code' => 'DUPLICATE',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/organizations', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }
}
