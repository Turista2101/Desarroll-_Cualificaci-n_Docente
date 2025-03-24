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
        Schema::create('informacion_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informacioncontacto_user_id')->constrained('users')->onDelete('cascade');//relacion de muchos a uno con la tabla usuarios
            $table->foreignId('informacioncontacto_municipio_id')->constrained('municipios')->onDelete('cascade');//relacion de muchos a uno con la tabla municipios
            $table->string('categoria_libreta_militar')->nullable();
            $table->string('numero_libreta_militar')->nullable();
            $table->string('numero_distrito_militar')->nullable();
            $table->string('direccion_residencia')->nullable();
            $table->string('barrrio')->nullable();
            $table->string('telefono_movil');
            $table->string('celular_alternativo')->nullable();
            $table->string('correo_alterno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informacion_contactos');
    }
};
