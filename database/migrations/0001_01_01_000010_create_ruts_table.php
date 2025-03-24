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
        Schema::create('ruts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rut_user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_rut');
            $table->string('razon_social');
            $table->string('tipo_persona');
            $table->string('codigo_ciiu');
            $table->string('Responsabilidades_tributarias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruts');
    }
};
