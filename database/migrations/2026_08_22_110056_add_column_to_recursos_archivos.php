<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recursos_archivos', function (Blueprint $table) {
            $table->longText('ocr')->nullable()->after('hash_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recursos_archivos', function (Blueprint $table) {
            $table->dropColumn('ocr');
        });
    }
};
