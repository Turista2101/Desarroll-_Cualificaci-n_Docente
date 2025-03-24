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
        Schema::create('eps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eps_user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_eps');
            $table->string('tipo_afiliacion');
            $table->string(('estado_afiliacion'));
            $table->date('fecha_afiliacion_efectiva');
            $table->date('fecha_finalizacion_afiliacion')->nullable();
            $table->string('tipo_afiliado');
            $table->string('numero_afiliado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eps');
    }
};
