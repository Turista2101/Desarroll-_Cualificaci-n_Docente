<?php

use App\Constants\ConstDocente\EstadoPuntajeDocente;
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
        Schema::create('puntajes', function (Blueprint $table) {
            $table->smallIncrements('id_puntaje');
            $table->integer(('puntaje_total'));
            $table->enum('estado_puntaje', EstadoPuntajeDocente::all());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntajes');
    }
};
