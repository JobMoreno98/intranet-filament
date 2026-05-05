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
        Schema::create('recursos_archivos', function (Blueprint $table) {
            $table->id();
            // Relación con el recurso padre
            $table->foreignId('recursos_id')->constrained('recursos')->onDelete('cascade');

            // Datos del archivo
            $table->string('path_original');
            $table->string('nombre_archivo_original')->nullable();
            $table->string('hash_archivo')->nullable(); // Útil para evitar duplicados

            // Lo que Go nos devolverá
            $table->json('assets_procesados')->nullable();
            $table->string('status')->default('pendiente'); // pendiente, en_cola, listo, error

            $table->integer('orden')->default(0); // Para que el usuario decida el orden de las fotos
            $table->timestamps();

            $table->index('recursos_id'); // Índice de clave foránea
            $table->index('status'); // Para filtros de "Pendientes/Listos"
            $table->index(['recursos_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos_archivos');
    }
};
