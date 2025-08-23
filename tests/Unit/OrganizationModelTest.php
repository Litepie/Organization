<?php

namespace Litepie\Organization\Tests\Unit;

use Litepie\Organization\Models\Organization;
use Litepie\Organization\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_has_correct_fillable_attributes()
    {
        $organization = new Organization();

        $expected = [
            'parent_id',
            'type',
            'name',
            'code',
            'description',
            'manager_id',
            'status',
            'created_by',
            'updated_by',
        ];

        $this->assertEquals($expected, $organization->getFillable());
    }

    public function test_organization_can_have_parent()
    {
        $parent = Organization::factory()->create();
        $child = Organization::factory()->childOf($parent)->create();

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertEquals($parent->name, $child->parent->name);
    }

    public function test_organization_can_have_children()
    {
        $parent = Organization::factory()->create();
        $child1 = Organization::factory()->childOf($parent)->create();
        $child2 = Organization::factory()->childOf($parent)->create();

        $children = $parent->children;

        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($child1));
        $this->assertTrue($children->contains($child2));
    }

    public function test_organization_scope_of_type()
    {
        Organization::factory()->company()->create();
        Organization::factory()->branch()->create();
        Organization::factory()->department()->create();

        $companies = Organization::ofType('company')->get();
        $branches = Organization::ofType('branch')->get();

        $this->assertCount(1, $companies);
        $this->assertCount(1, $branches);
        $this->assertEquals('company', $companies->first()->type);
        $this->assertEquals('branch', $branches->first()->type);
    }

    public function test_organization_scope_active()
    {
        Organization::factory()->active()->create();
        Organization::factory()->inactive()->create();

        $active = Organization::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('active', $active->first()->status);
    }

    public function test_organization_scope_root()
    {
        $root = Organization::factory()->create(['parent_id' => null]);
        $child = Organization::factory()->childOf($root)->create();

        $roots = Organization::root()->get();

        $this->assertCount(1, $roots);
        $this->assertEquals($root->id, $roots->first()->id);
    }

    public function test_organization_is_child_of()
    {
        $parent = Organization::factory()->create();
        $child = Organization::factory()->childOf($parent)->create();
        $other = Organization::factory()->create();

        $this->assertTrue($child->isChildOf($parent));
        $this->assertFalse($child->isChildOf($other));
        $this->assertFalse($parent->isChildOf($child));
    }

    public function test_organization_is_parent_of()
    {
        $parent = Organization::factory()->create();
        $child = Organization::factory()->childOf($parent)->create();
        $other = Organization::factory()->create();

        $this->assertTrue($parent->isParentOf($child));
        $this->assertFalse($parent->isParentOf($other));
        $this->assertFalse($child->isParentOf($parent));
    }

    public function test_organization_is_ancestor_of()
    {
        $grandparent = Organization::factory()->create();
        $parent = Organization::factory()->childOf($grandparent)->create();
        $child = Organization::factory()->childOf($parent)->create();

        $this->assertTrue($grandparent->isAncestorOf($child));
        $this->assertTrue($parent->isAncestorOf($child));
        $this->assertFalse($child->isAncestorOf($parent));
        $this->assertFalse($child->isAncestorOf($grandparent));
    }

    public function test_organization_is_descendant_of()
    {
        $grandparent = Organization::factory()->create();
        $parent = Organization::factory()->childOf($grandparent)->create();
        $child = Organization::factory()->childOf($parent)->create();

        $this->assertTrue($child->isDescendantOf($grandparent));
        $this->assertTrue($child->isDescendantOf($parent));
        $this->assertFalse($parent->isDescendantOf($child));
        $this->assertFalse($grandparent->isDescendantOf($child));
    }

    public function test_organization_gets_correct_depth()
    {
        $root = Organization::factory()->create();
        $level1 = Organization::factory()->childOf($root)->create();
        $level2 = Organization::factory()->childOf($level1)->create();
        $level3 = Organization::factory()->childOf($level2)->create();

        // Refresh to load relationships
        $root->refresh();
        $level1->refresh();
        $level2->refresh();
        $level3->refresh();

        $this->assertEquals(0, $root->depth);
        $this->assertEquals(1, $level1->depth);
        $this->assertEquals(2, $level2->depth);
        $this->assertEquals(3, $level3->depth);
    }

    public function test_organization_gets_correct_full_path()
    {
        $company = Organization::factory()->create(['name' => 'Acme Corp']);
        $branch = Organization::factory()->childOf($company)->create(['name' => 'NY Branch']);
        $dept = Organization::factory()->childOf($branch)->create(['name' => 'IT Dept']);

        // Refresh to load relationships
        $dept->refresh();

        $expectedPath = 'Acme Corp > NY Branch > IT Dept';
        $this->assertEquals($expectedPath, $dept->full_path);
    }

    public function test_organization_can_get_ancestors()
    {
        $root = Organization::factory()->create(['name' => 'Root']);
        $level1 = Organization::factory()->childOf($root)->create(['name' => 'Level 1']);
        $level2 = Organization::factory()->childOf($level1)->create(['name' => 'Level 2']);

        $ancestors = $level2->ancestors();

        $this->assertCount(2, $ancestors);
        $this->assertEquals('Level 1', $ancestors->first()->name);
        $this->assertEquals('Root', $ancestors->last()->name);
    }

    public function test_organization_tree_returns_root_organizations_with_children()
    {
        $root1 = Organization::factory()->create();
        $root2 = Organization::factory()->create();
        $child1 = Organization::factory()->childOf($root1)->create();

        $tree = Organization::tree();

        $this->assertCount(2, $tree);
        $this->assertTrue($tree->contains('id', $root1->id));
        $this->assertTrue($tree->contains('id', $root2->id));
    }
}
