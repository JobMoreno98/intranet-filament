<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('nacionalidad');
            $table->date('fecha_nacimiento');
            $table->string('sexo'); // hombre, mujer, prefiero_no_decirlo
            $table->string('ciudad_pais_residencia');
            $table->string('grado_estudios'); // sin_estudios, educacion_basica, bachillerato, licenciatura, posgrado
            $table->string('institucion_adscripcion')->nullable();
            $table->string('motivo_consulta'); // personal, escolar_tesis, investigacion_academica_profesional
            $table->text('tema_investigacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};