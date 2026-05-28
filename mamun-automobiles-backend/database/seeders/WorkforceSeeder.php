<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Skill;
use App\Models\User;
use App\Models\Employee;
use App\Constants\WorkforceConstants;

class WorkforceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Seed Branches
            $mainBranch = Branch::firstOrCreate(
                ['name' => 'Main Branch'],
                ['address' => 'Dhaka', 'phone' => '01700000001']
            );

            $mirpurBranch = Branch::firstOrCreate(
                ['name' => 'Mirpur Branch'],
                ['address' => 'Mirpur, Dhaka', 'phone' => '01700000002']
            );

            // 2. Seed Departments
            $mechDept = Department::firstOrCreate(
                ['code' => 'MECH'],
                [
                    'name' => 'Mechanical Department',
                    'description' => 'Handles engine, transmission, suspension, and overall mechanical repairs.',
                    'branch_id' => $mainBranch->id,
                    'is_active' => true
                ]
            );

            $elecDept = Department::firstOrCreate(
                ['code' => 'ELEC'],
                [
                    'name' => 'Electrical Department',
                    'description' => 'Wiring harnesses, ECU, diagnostics, and vehicle electronics.',
                    'branch_id' => $mainBranch->id,
                    'is_active' => true
                ]
            );

            $bodyDept = Department::firstOrCreate(
                ['code' => 'BODY'],
                [
                    'name' => 'Body & Paint Department',
                    'description' => 'Denting, painting, body customization, and exterior repair.',
                    'branch_id' => $mainBranch->id,
                    'is_active' => true
                ]
            );

            $adminDept = Department::firstOrCreate(
                ['code' => 'ADMIN'],
                [
                    'name' => 'Administrative Department',
                    'description' => 'HR, accounts, billing, customer relations, and support.',
                    'branch_id' => $mainBranch->id,
                    'is_active' => true
                ]
            );

            // 3. Seed Designations (Job Positions)
            $supervisorDesg = Designation::firstOrCreate(
                ['code' => 'SUPERVISOR'],
                [
                    'name' => 'Workshop Supervisor',
                    'department_id' => $mechDept->id,
                    'is_active' => true
                ]
            );

            $leadTechDesg = Designation::firstOrCreate(
                ['code' => 'TECH_LEAD'],
                [
                    'name' => 'Lead Technician',
                    'department_id' => $mechDept->id,
                    'is_active' => true
                ]
            );

            $asstTechDesg = Designation::firstOrCreate(
                ['code' => 'TECH_ASST'],
                [
                    'name' => 'Assistant Technician',
                    'department_id' => $mechDept->id,
                    'parent_designation_id' => $leadTechDesg->id,
                    'is_active' => true
                ]
            );

            $helperDesg = Designation::firstOrCreate(
                ['code' => 'HELPER'],
                [
                    'name' => 'Helper',
                    'department_id' => $mechDept->id,
                    'parent_designation_id' => $asstTechDesg->id,
                    'is_active' => true
                ]
            );

            $adminDesg = Designation::firstOrCreate(
                ['code' => 'ADMIN_STAFF'],
                [
                    'name' => 'Administrative Staff',
                    'department_id' => $adminDept->id,
                    'is_active' => true
                ]
            );

            // 4. Seed Skills
            $skillsData = [
                [
                    'code' => 'ENG_DIAG',
                    'name' => 'Engine Diagnostics',
                    'description' => 'Computerized diagnostics, scanning, and troubleshooting engine issues.'
                ],
                [
                    'code' => 'TRANS_OVER',
                    'name' => 'Transmission Overhaul',
                    'description' => 'Repair, rebuilding, and servicing automatic and manual transmissions.'
                ],
                [
                    'code' => 'AC_HEAT',
                    'name' => 'AC & Heating',
                    'description' => 'HVAC recharge, leak testing, evaporator, and compressor replacement.'
                ],
                [
                    'code' => 'ELEC_SYS',
                    'name' => 'Electrical Systems',
                    'description' => 'Wiring repair, sensor calibration, starter, and alternator repairs.'
                ],
                [
                    'code' => 'SUSP_BRAKE',
                    'name' => 'Suspension & Brakes',
                    'description' => 'Shock absorber, brake pads, rotor turning, steering box repairs.'
                ]
            ];

            $skills = [];
            foreach ($skillsData as $skillVal) {
                $skills[$skillVal['code']] = Skill::firstOrCreate(
                    ['code' => $skillVal['code']],
                    [
                        'name' => $skillVal['name'],
                        'description' => $skillVal['description']
                    ]
                );
            }

            // 5. Seed Employees & Link to Existing Users
            // Fetch users from AdminUserSeeder if they exist, or create them
            $userMapping = [
                [
                    'email' => 'admin@mamunerp.com',
                    'name' => 'Super Admin',
                    'role' => 'Super Admin',
                    'code' => 'EMP-0001',
                    'first' => 'Super',
                    'last' => 'Admin',
                    'phone' => '01700000001',
                    'dept' => $adminDept,
                    'desg' => $adminDesg,
                    'salary' => 50000.00,
                    'skills' => []
                ],
                [
                    'email' => 'manager@mamunerp.com',
                    'name' => 'Workshop Manager',
                    'role' => 'Manager',
                    'code' => 'EMP-0002',
                    'first' => 'Workshop',
                    'last' => 'Manager',
                    'phone' => '01700000002',
                    'dept' => $mechDept,
                    'desg' => $supervisorDesg,
                    'salary' => 45000.00,
                    'skills' => []
                ],
                [
                    'email' => 'tech@mamunerp.com',
                    'name' => 'Senior Technician',
                    'role' => 'Technician',
                    'code' => 'TECH-0001',
                    'first' => 'Senior',
                    'last' => 'Technician',
                    'phone' => '01711111111',
                    'dept' => $mechDept,
                    'desg' => $leadTechDesg,
                    'salary' => 35000.00,
                    'skills' => [
                        ['code' => 'ENG_DIAG', 'proficiency' => WorkforceConstants::SKILL_EXPERT],
                        ['code' => 'TRANS_OVER', 'proficiency' => WorkforceConstants::SKILL_SENIOR]
                    ]
                ],
                [
                    'email' => 'cashier@mamunerp.com',
                    'name' => 'Front Desk Cashier',
                    'role' => 'Cashier',
                    'code' => 'EMP-0003',
                    'first' => 'Front Desk',
                    'last' => 'Cashier',
                    'phone' => '01700000003',
                    'dept' => $adminDept,
                    'desg' => $adminDesg,
                    'salary' => 20000.00,
                    'skills' => []
                ],
                [
                    'email' => 'store@mamunerp.com',
                    'name' => 'Inventory Manager',
                    'role' => 'Store Manager',
                    'code' => 'EMP-0004',
                    'first' => 'Inventory',
                    'last' => 'Manager',
                    'phone' => '01700000004',
                    'dept' => $adminDept,
                    'desg' => $adminDesg,
                    'salary' => 25000.00,
                    'skills' => []
                ]
            ];

            foreach ($userMapping as $uData) {
                // Find or create user
                $user = User::where('email', $uData['email'])->first();
                if (!$user) {
                    $user = User::create([
                        'email' => $uData['email'],
                        'name' => $uData['name'],
                        'password' => Hash::make('password123'),
                        'phone' => $uData['phone'],
                        'is_active' => true,
                    ]);
                    $user->assignRole($uData['role']);
                }

                // Update legacy fields on User table for backward compatibility
                $user->update([
                    'department_id' => $uData['dept']->id,
                    'designation_id' => $uData['desg']->id,
                ]);

                // Create or update Employee profile
                $employee = Employee::updateOrCreate(
                    ['employee_code' => $uData['code']],
                    [
                        'user_id' => $user->id,
                        'department_id' => $uData['dept']->id,
                        'designation_id' => $uData['desg']->id,
                        'branch_id' => $mainBranch->id,
                        'first_name' => $uData['first'],
                        'last_name' => $uData['last'],
                        'phone' => $uData['phone'],
                        'address' => 'Dhaka, Bangladesh',
                        'nid' => '1234567890',
                        'salary' => $uData['salary'],
                        'joining_date' => now()->format('Y-m-d'),
                        'status' => WorkforceConstants::STATUS_ACTIVE,
                        'availability_status' => WorkforceConstants::AVAIL_AVAILABLE,
                    ]
                );

                // Attach skills to Employee
                if (!empty($uData['skills'])) {
                    $pivotData = [];
                    foreach ($uData['skills'] as $skillLink) {
                        $sModel = $skills[$skillLink['code']];
                        $pivotData[$sModel->id] = ['proficiency_level' => $skillLink['proficiency']];
                    }
                    $employee->skills()->sync($pivotData);
                }
            }

            // 6. Create extra workforce entries for testing skill matrices (e.g. helpers, assistant techs)
            $extraEmployees = [
                [
                    'email' => 'asst1@mamunerp.com',
                    'name' => 'Assistant Tech A',
                    'role' => 'Technician',
                    'code' => 'TECH-0002',
                    'first' => 'Assistant',
                    'last' => 'Tech A',
                    'phone' => '01722222222',
                    'dept' => $mechDept,
                    'desg' => $asstTechDesg,
                    'salary' => 22000.00,
                    'skills' => [
                        ['code' => 'ENG_DIAG', 'proficiency' => WorkforceConstants::SKILL_MID],
                        ['code' => 'AC_HEAT', 'proficiency' => WorkforceConstants::SKILL_SENIOR]
                    ]
                ],
                [
                    'email' => 'helper1@mamunerp.com',
                    'name' => 'Helper B',
                    'role' => 'Technician', // assign technician role for Spatie checks
                    'code' => 'HELP-0001',
                    'first' => 'Helper',
                    'last' => 'B',
                    'phone' => '01733333333',
                    'dept' => $mechDept,
                    'desg' => $helperDesg,
                    'salary' => 15000.00,
                    'skills' => [
                        ['code' => 'SUSP_BRAKE', 'proficiency' => WorkforceConstants::SKILL_JUNIOR]
                    ]
                ],
            ];

            foreach ($extraEmployees as $eData) {
                $user = User::where('email', $eData['email'])->first();
                if (!$user) {
                    $user = User::create([
                        'email' => $eData['email'],
                        'name' => $eData['name'],
                        'password' => Hash::make('password123'),
                        'phone' => $eData['phone'],
                        'is_active' => true,
                    ]);
                    $user->assignRole($eData['role']);
                }

                $user->update([
                    'department_id' => $eData['dept']->id,
                    'designation_id' => $eData['desg']->id,
                ]);

                $employee = Employee::updateOrCreate(
                    ['employee_code' => $eData['code']],
                    [
                        'user_id' => $user->id,
                        'department_id' => $eData['dept']->id,
                        'designation_id' => $eData['desg']->id,
                        'branch_id' => $mainBranch->id,
                        'first_name' => $eData['first'],
                        'last_name' => $eData['last'],
                        'phone' => $eData['phone'],
                        'address' => 'Dhaka, Bangladesh',
                        'nid' => '0987654321',
                        'salary' => $eData['salary'],
                        'joining_date' => now()->format('Y-m-d'),
                        'status' => WorkforceConstants::STATUS_ACTIVE,
                        'availability_status' => WorkforceConstants::AVAIL_AVAILABLE,
                    ]
                );

                if (!empty($eData['skills'])) {
                    $pivotData = [];
                    foreach ($eData['skills'] as $skillLink) {
                        $sModel = $skills[$skillLink['code']];
                        $pivotData[$sModel->id] = ['proficiency_level' => $skillLink['proficiency']];
                    }
                    $employee->skills()->sync($pivotData);
                }
            }

            // Update departments to assign head employee user IDs
            $mechManagerUser = User::where('email', 'manager@mamunerp.com')->first();
            if ($mechManagerUser) {
                $mechDept->update(['head_user_id' => $mechManagerUser->id]);
            }
            
            $adminUser = User::where('email', 'admin@mamunerp.com')->first();
            if ($adminUser) {
                $adminDept->update(['head_user_id' => $adminUser->id]);
            }
        });
    }
}
