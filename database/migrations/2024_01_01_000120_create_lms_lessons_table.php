<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('lms_course_sections')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->default('video');
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_provider')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
            $table->index(['section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_lessons');
    }
};
