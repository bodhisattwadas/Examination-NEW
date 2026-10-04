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
        Schema::create('exam_times', function (Blueprint $table) {
            $table->id();
            $table->string('label');           // e.g. "Morning (09:00 AM - 12:00 PM)"
            $table->string('value');           // e.g. "09:00 AM - 12:00 PM" (what gets stored/sent)
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_times');
    }
};
