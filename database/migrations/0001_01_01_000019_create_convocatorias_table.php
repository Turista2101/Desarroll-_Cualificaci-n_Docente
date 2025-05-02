<?php

use App\Constants\ConstTalentoHumano\EstadoConvocatoria;
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
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->smallIncrements('id_convocatoria');
            $table->string('nombre_convocatoria')->unique();
            $table->string('tipo');
            $table->date('fecha_publicacion');
            $table->date('fecha_cierre');
            $table->text('descripcion');
            $table->enum('estado_convocatoria', EstadoConvocatoria::all());
            $table->timestamps();

            
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
