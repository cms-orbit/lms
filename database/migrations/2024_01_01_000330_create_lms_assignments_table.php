<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('lms_course_sections')->nullOnDelete();
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->unsignedInteger('max_points')->default(100);
            $table->unsignedInteger('pass_points')->default(0);
            $table->timestamp('due_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
