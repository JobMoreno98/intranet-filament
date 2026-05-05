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
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();

            // 1. Relación con la "Plantilla" (Colección)
            // Usamos foreignId para que MySQL optimice la unión de tablas
            $table->foreignId('sub_colection_id')->constrained('sub_coleccions')->cascadeOnDelete();

            $table->string('fondo')->index();
            $table->integer('claveFondo')->unique();
            $table->string('tipo_media'); // pdf, imagen, video, audio

            // 2. Metadatos Bibliográficos (Los "buscables" rápido)
            $table->string('titulo')->index(); // Añadí index para búsquedas rápidas
            $table->string('autor')->nullable()->index();
            $table->integer('anio')->nullable()->index();

            // 3. Flexibilidad y Procesamiento
            $table->json('metadata')->nullable(); // Aquí va TODO lo extra que definas en el CMS
            $table->json('assets_procesados')->nullable(); // Aquí escribirá Go (HLS, WebP, etc.)

            // 4. Control
            $table->string('status')->default('pendiente')->index();
            $table->string('hash_archivo', 64)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('sub_colection_id');
            $table->fulltext('titulo');
            $table->index('claveFondo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
