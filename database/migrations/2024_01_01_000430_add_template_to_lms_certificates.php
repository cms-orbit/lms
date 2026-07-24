<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_certificates', function (Blueprint $table): void {
            $table->foreignId('certificate_template_id')->nullable()->after('course_id')
                ->constrained('lms_certificate_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_certificates', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('certificate_template_id');
        });
    }
};
