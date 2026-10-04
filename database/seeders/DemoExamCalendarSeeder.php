<?php

namespace Database\Seeders;

use App\Models\ExamDuty;
use App\Models\ExamDutyStaff;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoExamCalendarSeeder extends Seeder
{
    /**
     * Seed a realistic exam calendar example for a single teacher.
     */
    public function run(): void
    {
        DB::transaction(function () {
            ExamDutyStaff::query()->delete();
            ExamDuty::query()->delete();

            $teacher = Staff::where('staff_code', 'T001')->first();

            if (! $teacher) {
                $this->command->warn('Teacher T001 not found. Please seed staff data first.');
                return;
            }

            $timeSlots = [
                ['label' => 'Morning (09:00 AM - 12:00 PM)', 'value' => '09:00 AM - 12:00 PM'],
                ['label' => 'Afternoon (12:00 PM - 03:00 PM)', 'value' => '12:00 PM - 03:00 PM'],
                ['label' => 'Evening (03:00 PM - 06:00 PM)', 'value' => '03:00 PM - 06:00 PM'],
            ];

            foreach ($timeSlots as $sort => $time) {
                \App\Models\ExamTime::updateOrCreate(
                    ['value' => $time['value']],
                    [
                        'label' => $time['label'],
                        'sort_order' => $sort + 1,
                        'is_active' => true,
                    ]
                );
            }

            $examDates = [
                [
                    'date' => '2026-09-23',
                    'time' => '09:00 AM - 12:00 PM',
                    'role' => 'secretary',
                    'remarks' => 'Semester paper evaluation duty',
                ],
                [
                    'date' => '2026-09-24',
                    'time' => '12:00 PM - 03:00 PM',
                    'role' => 'duty',
                    'remarks' => 'Invigilation support',
                ],
                [
                    'date' => '2026-09-25',
                    'time' => '03:00 PM - 06:00 PM',
                    'role' => 'oic',
                    'remarks' => 'OIC duty for evening session',
                ],
            ];

            foreach ($examDates as $index => $entry) {
                $exam = ExamDuty::create([
                    'exam_name' => 'Sem-3 2026',
                    'exam_date' => $entry['date'],
                    'exam_time' => $entry['time'],
                    'default_hours' => 3.00,
                    'remarks' => $entry['remarks'],
                ]);

                ExamDutyStaff::create([
                    'exam_duty_id' => $exam->id,
                    'staff_id' => $teacher->id,
                    'is_duty' => $entry['role'] === 'duty' || $entry['role'] === 'secretary',
                    'is_exam_secretary' => $entry['role'] === 'secretary',
                    'is_oic' => $entry['role'] === 'oic',
                    'duty_hours' => 3.00,
                ]);
            }
        });

        $this->command->info('Demo exam calendar seeded for staff T001.');
    }
}
