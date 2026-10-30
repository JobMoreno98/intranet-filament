<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recursos', function (Blueprint $table) {
            Schema::table('recursos', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            DB::table('recursos')->orderBy('id')->chunkById(500, function ($filas) {
                foreach ($filas as $fila) {
                    DB::table('recursos')
                        ->where('id', $fila->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

            Schema::table('recursos', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->unique()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recursos', function (Blueprint $table) {
            Schema::table('recursos', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        });
    }
};
