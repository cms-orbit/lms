<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_certificate_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('orientation')->default('landscape');
            $table->unsignedInteger('width')->default(1123);
            $table->unsignedInteger('height')->default(794);
            $table->string('background')->nullable();
            $table->json('elements')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_certificate_templates');
    }
};
