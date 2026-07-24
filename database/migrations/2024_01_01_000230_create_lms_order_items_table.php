<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('lms_orders')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedTinyInteger('commission_rate')->default(0);
            $table->decimal('instructor_earning', 10, 2)->default(0);
            $table->decimal('admin_earning', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_order_items');
    }
};
