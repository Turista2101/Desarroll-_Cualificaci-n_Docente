<?php

use App\Constants\ConstEps\EstadoAfiliacion;
use App\Constants\ConstEps\TipoAfiliacion;
use App\Constants\ConstEps\TipoAfiliado;
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
            $table->smallIncrements('id_eps');
            $table->unsignedBigInteger('user_id');//relacion de muchos a uno con la tabla usuarios
            $table->string('nombre_eps');
            $table->enum('tipo_afiliacion', TipoAfiliacion::all());
            $table->enum('estado_afiliacion',EstadoAfiliacion::all());
            $table->date('fecha_afiliacion_efectiva');
            $table->date('fecha_finalizacion_afiliacion')->nullable();
            $table->enum('tipo_afiliado',TipoAfiliado::all());
            $table->string('numero_afiliado')->nullable();
            $table->timestamps();
            //llave foranea de la tabla usuarios
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
        Schema::dropIfExists('eps');
    }
};
