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
            $table->boolean('drip_enabled')->default(false)->after('commission_rate');
            $table->string('drip_type')->default('off')->after('drip_enabled');
            $table->boolean('player_disable_seek')->default(false)->after('drip_type');
            $table->boolean('player_disable_fastforward')->default(false)->after('player_disable_seek');
            $table->boolean('player_autoplay')->default(false)->after('player_disable_fastforward');
            $table->boolean('player_require_completion')->default(false)->after('player_autoplay');
        });
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table): void {
            $table->dropColumn([
                'drip_enabled',
                'drip_type',
                'player_disable_seek',
                'player_disable_fastforward',
                'player_autoplay',
                'player_require_completion',
            ]);
        });
    }
};
