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
        Schema::create('reader_sessions', function (Blueprint $table) {

            $table->id();

            $table->uuid('session_id')->unique();

            $table->foreignId('recurso_id');

            $table->ipAddress()->nullable();

            $table->string('user_agent')->nullable();

            $table->timestamp('expires_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reader_sessions');
    }
};
