<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_active');
            $table->foreignId('parent_plan_id')->nullable()->after('is_custom')->constrained('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropForeign(['parent_plan_id']);
            $table->dropColumn(['is_custom', 'parent_plan_id']);
        });
    }
};
