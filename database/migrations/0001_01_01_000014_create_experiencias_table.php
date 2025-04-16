<?php

use App\Constants\ConstAgregarExperiencia\TiposExperiencia;
use App\Constants\ConstAgregarExperiencia\TrabajoActual;
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
        // Create the 'experiencias' table
        //queda pendiente lo de ver que cada docente tenga experiencia uniautonoma.
        //falta hacer relacion con la tabla de usuarios
        Schema::create('experiencias', function (Blueprint $table) {
            $table->smallIncrements('id_experiencia');
            $table->enum('tipo_experiencia',TiposExperiencia::all());
            $table->string('institucion_experiencia');
            $table->string('cargo');
            $table->enum('trabajo_actual', TrabajoActual::all())->nullable();
            $table->tinyInteger('intensidad_horaria')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_finalizacion')->nullable();
            $table->date('fecha_expedicion_certificado')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiencias');
    }
};
