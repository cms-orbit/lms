<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_lessons', function (Blueprint $table): void {
            $table->unsignedInteger('drip_days')->nullable()->after('is_preview');
            $table->timestamp('drip_date')->nullable()->after('drip_days');
            $table->foreignId('drip_prerequisite_id')->nullable()->after('drip_date')
                ->constrained('lms_lessons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_lessons', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('drip_prerequisite_id');
            $table->dropColumn(['drip_days', 'drip_date']);
        });
    }
};
