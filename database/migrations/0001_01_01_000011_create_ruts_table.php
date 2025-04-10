<?php

use App\Constants\ConstRut\CodigoCiiu;
use App\Constants\ConstRut\TipoPersona;
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
            $table->smallIncrements('id_rut');
            $table->string('razon_social');
            $table->enum('tipo_persona',TipoPersona::all());
            $table->enum('codigo_ciiu', CodigoCiiu::all() );
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
