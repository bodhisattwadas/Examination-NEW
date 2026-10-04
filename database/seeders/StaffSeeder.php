<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing staff and related duty assignments for fresh dummy data
        \App\Models\ExamDutyStaff::query()->delete();
        \App\Models\Staff::query()->delete();

        $staffs = [
            // 6 Teaching Staff
            [
                'staff_code' => 'T001',
                'name' => 'Dr. Rajesh Kumar',
                'staff_type' => 'Teaching',
                'department' => 'Computer Science',
                'mobile' => '9876543210',
                'email' => 'rajesh.kumar@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'T002',
                'name' => 'Prof. Sunita Sharma',
                'staff_type' => 'Teaching',
                'department' => 'Mathematics',
                'mobile' => '9876543211',
                'email' => 'sunita.sharma@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'T003',
                'name' => 'Dr. Anil Verma',
                'staff_type' => 'Teaching',
                'department' => 'Physics',
                'mobile' => '9876543212',
                'email' => 'anil.verma@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'T004',
                'name' => 'Mrs. Meena Gupta',
                'staff_type' => 'Teaching',
                'department' => 'Chemistry',
                'mobile' => '9876543213',
                'email' => 'meena.gupta@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'T005',
                'name' => 'Dr. Vikram Singh',
                'staff_type' => 'Teaching',
                'department' => 'Electronics',
                'mobile' => '9876543214',
                'email' => 'vikram.singh@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'T006',
                'name' => 'Prof. Kavita Joshi',
                'staff_type' => 'Teaching',
                'department' => 'English',
                'mobile' => '9876543215',
                'email' => 'kavita.joshi@college.edu',
                'status' => 'Inactive',
            ],

            // 4 Non-Teaching Staff
            [
                'staff_code' => 'NT001',
                'name' => 'Mr. Ramesh Yadav',
                'staff_type' => 'Non-teaching',
                'department' => 'Administration',
                'mobile' => '9876543216',
                'email' => 'ramesh.yadav@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'NT002',
                'name' => 'Mrs. Sushma Devi',
                'staff_type' => 'Non-teaching',
                'department' => 'Library',
                'mobile' => '9876543217',
                'email' => 'sushma.devi@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'NT003',
                'name' => 'Mr. Deepak Kumar',
                'staff_type' => 'Non-teaching',
                'department' => 'Accounts',
                'mobile' => '9876543218',
                'email' => 'deepak.kumar@college.edu',
                'status' => 'Active',
            ],
            [
                'staff_code' => 'NT004',
                'name' => 'Ms. Pooja Rani',
                'staff_type' => 'Non-teaching',
                'department' => 'Laboratory',
                'mobile' => '9876543219',
                'email' => 'pooja.rani@college.edu',
                'status' => 'Active',
            ],
        ];

        foreach ($staffs as $staff) {
            \App\Models\Staff::create($staff);
        }
    }
}
