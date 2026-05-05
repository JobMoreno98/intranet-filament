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
        Schema::create('sub_coleccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coleccion_id')->constrained('coleccions')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('descripcion');
            $table->tinyInteger('order')->default(1);
            $table->json('esquema')->nullable(); // Aquí se guarda el diseño del formulario
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_coleccions');
    }
};
