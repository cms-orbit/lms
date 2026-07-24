<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table): void {
            $table->boolean('is_free')->default(true)->after('status');
            $table->decimal('price', 10, 2)->default(0)->after('is_free');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->string('currency', 3)->default('USD')->after('sale_price');
            $table->unsignedTinyInteger('commission_rate')->nullable()->after('currency')
                ->comment('Instructor earning percentage; null falls back to lms.marketplace.commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table): void {
            $table->dropColumn(['is_free', 'price', 'sale_price', 'currency', 'commission_rate']);
        });
    }
};
