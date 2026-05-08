<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('microservice_id')->constrained();
            $table->integer('quantity')->default(1);
            $table->decimal('custom_price', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'microservice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_services');
        Schema::dropIfExists('plans');
    }
};
