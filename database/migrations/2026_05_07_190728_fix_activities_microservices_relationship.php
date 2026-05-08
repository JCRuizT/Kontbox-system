<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrección: Un Microservicio tiene muchas Actividades (no al revés).
     * - Se agrega microservice_id a activities (FK)
     * - Se elimina activity_id de microservices (relación incorrecta)
     */
    public function up(): void
    {
        // Restaurar microservice_id en activities
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('microservice_id')->nullable()->after('id')->constrained('microservices')->cascadeOnDelete();
        });

        // Quitar activity_id de microservices (relación incorrecta)
        Schema::table('microservices', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn('activity_id');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['microservice_id']);
            $table->dropColumn('microservice_id');
        });

        Schema::table('microservices', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->after('is_active')->constrained('activities')->nullOnDelete();
        });
    }
};
