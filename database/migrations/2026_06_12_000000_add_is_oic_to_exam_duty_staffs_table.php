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
        Schema::table('exam_duty_staffs', function (Blueprint $table) {
            $table->boolean('is_oic')->default(false)->after('is_exam_secretary');
            $table->unsignedInteger('oic_count')->default(0)->after('is_oic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_duty_staffs', function (Blueprint $table) {
            $table->dropColumn('is_oic');
        });
    }
};
