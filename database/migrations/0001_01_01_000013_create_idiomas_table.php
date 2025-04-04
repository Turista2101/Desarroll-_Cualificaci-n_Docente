<?php

use App\Constants\ConstAgregarIdioma\NivelIdioma;
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
        Schema::create('idiomas', function (Blueprint $table) {
            $table->smallIncrements('id_idioma');
            $table->string('idioma');
            $table->string('institucion_idioma');
            $table->date('fecha_certificado')->nullable();
            $table->enum('nivel', NivelIdioma::all());
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idiomas');
    }
};
