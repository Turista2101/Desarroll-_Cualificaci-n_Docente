<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // crea la tabla departamentos
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->foreignId('pais_id')->constrained('paises')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    // elimina la tabla departamentos
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
