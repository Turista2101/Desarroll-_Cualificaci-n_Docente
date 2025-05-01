<?php

use App\Constants\ConstDocente\EstadoEvaluacionDocente;
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
        Schema::create('evaluacion_docentes', function (Blueprint $table) {
            $table->smallIncrements('id_evaluacion_docente');
            $table->unsignedBigInteger('user_id');
            $table->decimal('promedio_evaluacion_docente', 3, 1);
            $table->enum('estado_evaluacion_docente',EstadoEvaluacionDocente::all())->default('Pendiente');
            $table->timestamps();
            // Relación con la tabla de usuarios
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
        Schema::dropIfExists('evaluacion_docentes');
    }
};
