<?php

namespace Litepie\Organization\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Litepie\Organization\Models\Organization;

class OrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['company', 'branch', 'department', 'division', 'sub_division']),
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->unique()->lexify('???-???')),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => 1, // Default to user ID 1, should be overridden in tests
        ];
    }

    /**
     * Indicate that the organization is a company.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'company',
            'parent_id' => null,
            'name' => $this->faker->company() . ' Corporation',
        ]);
    }

    /**
     * Indicate that the organization is a branch.
     */
    public function branch(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'branch',
            'name' => $this->faker->city() . ' Branch',
        ]);
    }

    /**
     * Indicate that the organization is a department.
     */
    public function department(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'department',
            'name' => $this->faker->randomElement([
                'Human Resources',
                'Information Technology',
                'Finance',
                'Marketing',
                'Operations',
                'Sales',
                'Customer Service',
                'Research & Development'
            ]) . ' Department',
        ]);
    }

    /**
     * Indicate that the organization is a division.
     */
    public function division(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'division',
            'name' => $this->faker->randomElement([
                'North',
                'South',
                'East',
                'West',
                'Central',
                'International',
                'Domestic'
            ]) . ' Division',
        ]);
    }

    /**
     * Indicate that the organization is a sub-division.
     */
    public function subDivision(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sub_division',
            'name' => $this->faker->randomElement([
                'Alpha',
                'Beta',
                'Gamma',
                'Delta',
                'Team A',
                'Team B',
                'Unit 1',
                'Unit 2'
            ]) . ' Sub-Division',
        ]);
    }

    /**
     * Indicate that the organization is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Set a specific parent for the organization.
     */
    public function childOf(Organization $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Set a specific manager for the organization.
     */
    public function managedBy(int $managerId): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $managerId,
        ]);
    }

    /**
     * Create a complete organization hierarchy.
     */
    public function createHierarchy(): Organization
    {
        // Create company
        $company = $this->company()->create();

        // Create branches
        $branches = $this->branch()->childOf($company)->count(2)->create();

        foreach ($branches as $branch) {
            // Create departments for each branch
            $departments = $this->department()->childOf($branch)->count(3)->create();

            foreach ($departments as $department) {
                // Create divisions for each department
                $divisions = $this->division()->childOf($department)->count(2)->create();

                foreach ($divisions as $division) {
                    // Create sub-divisions for each division
                    $this->subDivision()->childOf($division)->count(2)->create();
                }
            }
        }

        return $company;
    }
}
