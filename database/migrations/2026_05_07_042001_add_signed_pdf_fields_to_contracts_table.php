<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('signed_pdf_path')->nullable()->after('signed_pdf_uploaded_at');
            $table->string('signed_pdf_original_name')->nullable()->after('signed_pdf_path');
            $table->unsignedInteger('signed_pdf_size')->nullable()->after('signed_pdf_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['signed_pdf_path', 'signed_pdf_original_name', 'signed_pdf_size']);
        });
    }
};
