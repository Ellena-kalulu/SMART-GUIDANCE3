<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->text('description');
            $table->string('amount')->nullable();          // e.g. "MK500,000/year" or "Full tuition"
            $table->string('eligibility_path')->nullable(); // Science, Humanities, Commerce, Any
            $table->decimal('min_grade', 5, 1)->default(50); // minimum average grade %
            $table->string('eligible_careers')->nullable(); // CSV list e.g. "Doctor,Nurse,Pharmacist"
            $table->string('application_url')->nullable();
            $table->string('deadline')->nullable();         // e.g. "March each year"
            $table->enum('type', ['government', 'university', 'ngo', 'private', 'international'])->default('government');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
