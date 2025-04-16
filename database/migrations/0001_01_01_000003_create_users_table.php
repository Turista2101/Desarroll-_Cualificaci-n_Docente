<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\ConstUsuario\TipoIdentificacion;
use App\Constants\ConstUsuario\Genero;
use App\Constants\ConstUsuario\EstadoCivil;




return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // tabla para gestionar los usuarios
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('municipio_id');
            $table->enum('tipo_identificacion', TipoIdentificacion::all());
            $table->string('numero_identificacion')->unique();
            $table->enum('genero', Genero::all())->nullable();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('estado_civil',EstadoCivil::all())->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken(); //para mantener la sesión abierta
            $table->timestamps();
            
            $table->foreign('municipio_id')
                ->references('id_municipio')
                ->on('municipios');

        });

        // tabla para restablecer la contraseña

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });


        //Gestiona la sesion activa del usuario

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
