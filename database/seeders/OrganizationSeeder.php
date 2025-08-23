<?php

namespace Litepie\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Litepie\Organization\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample organization hierarchy
        $this->createSampleHierarchy();
    }

    /**
     * Create a comprehensive sample organization hierarchy.
     */
    protected function createSampleHierarchy(): void
    {
        // Create main company
        $company = Organization::create([
            'type' => 'company',
            'name' => 'Acme Corporation',
            'code' => 'ACME',
            'description' => 'A leading technology company providing innovative solutions.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create branches
        $nyBranch = Organization::create([
            'parent_id' => $company->id,
            'type' => 'branch',
            'name' => 'New York Branch',
            'code' => 'ACME-NY',
            'description' => 'Main branch located in New York City.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $laBranch = Organization::create([
            'parent_id' => $company->id,
            'type' => 'branch',
            'name' => 'Los Angeles Branch',
            'code' => 'ACME-LA',
            'description' => 'West coast operations center.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create departments for NY Branch
        $itDept = Organization::create([
            'parent_id' => $nyBranch->id,
            'type' => 'department',
            'name' => 'Information Technology',
            'code' => 'ACME-NY-IT',
            'description' => 'Technology and infrastructure management.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $hrDept = Organization::create([
            'parent_id' => $nyBranch->id,
            'type' => 'department',
            'name' => 'Human Resources',
            'code' => 'ACME-NY-HR',
            'description' => 'Employee relations and talent management.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $financeDept = Organization::create([
            'parent_id' => $nyBranch->id,
            'type' => 'department',
            'name' => 'Finance',
            'code' => 'ACME-NY-FIN',
            'description' => 'Financial planning and accounting.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create departments for LA Branch
        $salesDept = Organization::create([
            'parent_id' => $laBranch->id,
            'type' => 'department',
            'name' => 'Sales',
            'code' => 'ACME-LA-SALES',
            'description' => 'Customer acquisition and relationship management.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $marketingDept = Organization::create([
            'parent_id' => $laBranch->id,
            'type' => 'department',
            'name' => 'Marketing',
            'code' => 'ACME-LA-MKT',
            'description' => 'Brand management and promotional activities.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create divisions for IT Department
        $devDiv = Organization::create([
            'parent_id' => $itDept->id,
            'type' => 'division',
            'name' => 'Software Development',
            'code' => 'ACME-NY-IT-DEV',
            'description' => 'Application development and programming.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $infraDiv = Organization::create([
            'parent_id' => $itDept->id,
            'type' => 'division',
            'name' => 'Infrastructure',
            'code' => 'ACME-NY-IT-INFRA',
            'description' => 'Server management and network operations.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create divisions for Sales Department
        $domesticSales = Organization::create([
            'parent_id' => $salesDept->id,
            'type' => 'division',
            'name' => 'Domestic Sales',
            'code' => 'ACME-LA-SALES-DOM',
            'description' => 'Sales operations within the United States.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        $intlSales = Organization::create([
            'parent_id' => $salesDept->id,
            'type' => 'division',
            'name' => 'International Sales',
            'code' => 'ACME-LA-SALES-INTL',
            'description' => 'Global sales operations and partnerships.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create sub-divisions for Software Development
        Organization::create([
            'parent_id' => $devDiv->id,
            'type' => 'sub_division',
            'name' => 'Frontend Team',
            'code' => 'ACME-NY-IT-DEV-FE',
            'description' => 'User interface and user experience development.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        Organization::create([
            'parent_id' => $devDiv->id,
            'type' => 'sub_division',
            'name' => 'Backend Team',
            'code' => 'ACME-NY-IT-DEV-BE',
            'description' => 'Server-side application development.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        Organization::create([
            'parent_id' => $devDiv->id,
            'type' => 'sub_division',
            'name' => 'Mobile Team',
            'code' => 'ACME-NY-IT-DEV-MOB',
            'description' => 'Mobile application development.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create sub-divisions for Infrastructure
        Organization::create([
            'parent_id' => $infraDiv->id,
            'type' => 'sub_division',
            'name' => 'Network Operations',
            'code' => 'ACME-NY-IT-INFRA-NET',
            'description' => 'Network monitoring and maintenance.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        Organization::create([
            'parent_id' => $infraDiv->id,
            'type' => 'sub_division',
            'name' => 'Cloud Services',
            'code' => 'ACME-NY-IT-INFRA-CLOUD',
            'description' => 'Cloud infrastructure management.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create sub-divisions for Domestic Sales
        Organization::create([
            'parent_id' => $domesticSales->id,
            'type' => 'sub_division',
            'name' => 'Enterprise Sales',
            'code' => 'ACME-LA-SALES-DOM-ENT',
            'description' => 'Large enterprise customer accounts.',
            'status' => 'active',
            'created_by' => 1,
        ]);

        Organization::create([
            'parent_id' => $domesticSales->id,
            'type' => 'sub_division',
            'name' => 'SMB Sales',
            'code' => 'ACME-LA-SALES-DOM-SMB',
            'description' => 'Small and medium business accounts.',
            'status' => 'active',
            'created_by' => 1,
        ]);
    }
}
