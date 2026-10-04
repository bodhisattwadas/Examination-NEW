<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Default exam time slots for the dropdown in duty entry
        if (\App\Models\ExamTime::count() === 0) {
            \App\Models\ExamTime::insert([
                ['label' => 'Morning (09:00 AM - 12:00 PM)', 'value' => '09:00 AM - 12:00 PM', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Afternoon (12:00 PM - 03:00 PM)', 'value' => '12:00 PM - 03:00 PM', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Evening (03:00 PM - 06:00 PM)', 'value' => '03:00 PM - 06:00 PM', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Note: Run `php artisan db:seed` (or migrate:fresh --seed) to populate default exam time slots if the table is empty.
    }
}
