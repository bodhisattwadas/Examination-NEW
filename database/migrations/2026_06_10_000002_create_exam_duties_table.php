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
        Schema::create('exam_duties', function (Blueprint $table) {
            $table->id();
            $table->string('exam_name', 150);
            $table->date('exam_date')->nullable();
            $table->string('exam_time', 100)->nullable();
            $table->decimal('default_hours', 5, 2)->default(3.00);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_duties');
    }
};
