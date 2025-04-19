<?php

use App\Constants\ConstTalentoHumano\AreasContratacion;
use App\Constants\ConstTalentoHumano\TipoContratacion;
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
        Schema::create('contratacions', function (Blueprint $table) {
            $table->smallIncrements('id_contratacion');
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo_contrato', TipoContratacion::all());
            $table->enum('area', AreasContratacion::all());
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('valor_contrato');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // Relación con la tabla de usuarios
            $table->foreign('user_id')
                ->references('id')
                ->on('users'); // Eliminar contratacion si se elimina el usuario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratacions');
    }
};
