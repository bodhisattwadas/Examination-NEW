<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\ExamDuty;
use App\Models\ExamDutyStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamDutyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test active staff selection table only displays active staff.
     */
    public function test_only_active_staff_displayed_in_duty_entry_list(): void
    {
        // Create an active and an inactive staff member
        $activeStaff = Staff::create([
            'staff_code' => 'T101',
            'name' => 'Active Teacher',
            'staff_type' => 'Teaching',
            'department' => 'Science',
            'status' => 'Active',
        ]);

        $inactiveStaff = Staff::create([
            'staff_code' => 'T102',
            'name' => 'Inactive Teacher',
            'staff_type' => 'Teaching',
            'department' => 'Science',
            'status' => 'Inactive',
        ]);

        // Access the duty entry page
        $response = $this->get(route('duty.create'));

        $response.assertStatus(200);

        // Verify active staff is present, inactive staff is absent
        $response->assertSee('Active Teacher');
        $response->assertSee('T101');
        $response->assertDontSee('Inactive Teacher');
        $response->assertDontSee('T102');
    }

    /**
     * Test successful exam duty assignment.
     */
    public function test_exam_duty_assignment_succeeds_with_valid_data(): void
    {
        $staff1 = Staff::create([
            'staff_code' => 'T101',
            'name' => 'John Doe',
            'staff_type' => 'Teaching',
            'status' => 'Active',
        ]);

        $staff2 = Staff::create([
            'staff_code' => 'T102',
            'name' => 'Jane Smith',
            'staff_type' => 'Non-teaching',
            'status' => 'Active',
        ]);

        $payload = [
            'exam_name' => 'Sem-1',
            'exam_date' => '2026-06-12',
            'exam_time' => '09:00 AM - 12:00 PM',
            'default_hours' => 3.00,
            'remarks' => 'Urgent duty',
            'duties' => [
                $staff1->id => [
                    'is_duty' => '1',
                    'is_exam_secretary' => '0',
                ],
                $staff2->id => [
                    'is_duty' => '1',
                    'is_exam_secretary' => '1',
                ],
            ]
        ];

        $response = $this->post(route('duty.store'), $payload);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('exam_duties', [
            'exam_name' => 'Sem-1',
            'exam_date' => '2026-06-12',
            'exam_time' => '09:00 AM - 12:00 PM',
        ]);

        $duty = ExamDuty::first();

        // Check pivot allocations
        $this->assertDatabaseHas('exam_duty_staffs', [
            'exam_duty_id' => $duty->id,
            'staff_id' => $staff1->id,
            'is_duty' => 1,
            'is_exam_secretary' => 0,
            'duty_hours' => 3.00,
        ]);

        $this->assertDatabaseHas('exam_duty_staffs', [
            'exam_duty_id' => $duty->id,
            'staff_id' => $staff2->id,
            'is_duty' => 1,
            'is_exam_secretary' => 1,
            'duty_hours' => 3.00,
        ]);
    }

    /**
     * Test duplicate duty check logic prevents saving.
     */
    public function test_duplicate_duty_assignment_is_prevented(): void
    {
        $staff = Staff::create([
            'staff_code' => 'T101',
            'name' => 'John Doe',
            'staff_type' => 'Teaching',
            'status' => 'Active',
        ]);

        // Pre-create a duty
        $duty = ExamDuty::create([
            'exam_name' => 'Sem-1',
            'exam_date' => '2026-06-12',
            'exam_time' => '09:00 AM - 12:00 PM',
            'default_hours' => 3.00,
        ]);

        ExamDutyStaff::create([
            'exam_duty_id' => $duty->id,
            'staff_id' => $staff->id,
            'is_duty' => 1,
            'is_exam_secretary' => 0,
            'duty_hours' => 3.00,
        ]);

        // Attempt to save the exact same duty for the same staff
        $payload = [
            'exam_name' => 'Sem-1',
            'exam_date' => '2026-06-12',
            'exam_time' => '09:00 AM - 12:00 PM',
            'default_hours' => 3.00,
            'duties' => [
                $staff->id => [
                    'is_duty' => '1',
                ]
            ]
        ];

        $response = $this->post(route('duty.store'), $payload);

        // Should return back with validation/error message
        $response->assertStatus(302);
        $response->assertSessionHas('error');
        
        $errorMsg = session('error');
        $this->assertStringContainsString('Conflict Detected', $errorMsg);
        $this->assertStringContainsString('John Doe', $errorMsg);

        // Ensure there is only 1 assignment in the pivot table
        $this->assertEquals(1, ExamDutyStaff::count());
    }

    /**
     * Test at least one staff must be selected rule.
     */
    public function test_at_least_one_staff_must_be_selected(): void
    {
        $staff = Staff::create([
            'staff_code' => 'T101',
            'name' => 'John Doe',
            'staff_type' => 'Teaching',
            'status' => 'Active',
        ]);

        $payload = [
            'exam_name' => 'Sem-1',
            'exam_date' => '2026-06-12',
            'exam_time' => '09:00 AM - 12:00 PM',
            'default_hours' => 3.00,
            'duties' => [
                $staff->id => [
                    'is_duty' => '0',
                    'is_exam_secretary' => '0',
                ]
            ]
        ];

        $response = $this->post(route('duty.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('At least one staff member must be selected', session('error'));
    }
}
