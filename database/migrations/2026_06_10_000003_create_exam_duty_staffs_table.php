<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_duty_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_duty_id')->constrained('exam_duties')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staffs')->onDelete('cascade');
            $table->boolean('is_duty')->default(false);
            $table->boolean('is_exam_secretary')->default(false);
            $table->decimal('duty_hours', 5, 2)->default(3.00);
            $table->timestamps();

            $table->unique(['exam_duty_id', 'staff_id'], 'unique_exam_staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_duty_staffs');
    }
};
