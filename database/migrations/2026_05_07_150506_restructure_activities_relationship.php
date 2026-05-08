<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('microservices', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->after('is_active')->constrained('activities')->nullOnDelete();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['microservice_id']);
            $table->dropColumn('microservice_id');
        });
    }

    public function down(): void
    {
        Schema::table('microservices', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn('activity_id');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('microservice_id')->nullable()->after('id')->constrained('microservices')->cascadeOnDelete();
        });
    }
};
