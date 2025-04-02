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
        Schema::create('documentos', function (Blueprint $table) {
            $table->smallIncrements('id_documento');
            $table->unsignedBigInteger('user_id');
            $table->string('archivo'); // Ruta del archivo
            $table->string('estado')->default('pendiente'); // Estado como string
            $table->morphs('documentable');// Relación polimórfica
            $table->timestamps();
            // llaves foraneas
            $table->foreign('user_id')
            ->references('id')
            ->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
