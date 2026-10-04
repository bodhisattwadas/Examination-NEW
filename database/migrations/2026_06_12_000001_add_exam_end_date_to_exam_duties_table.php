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
        Schema::table('exam_duties', function (Blueprint $table) {
            $table->date('exam_end_date')->nullable()->after('exam_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_duties', function (Blueprint $table) {
            $table->dropColumn('exam_end_date');
        });
    }
};
