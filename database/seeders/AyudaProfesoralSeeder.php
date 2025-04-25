<?php

namespace Database\Seeders;

use App\Models\Usuario\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AyudaProfesoralSeeder extends Seeder
{
    
    public function run(): void
    {
        //Crear usuario ayudaProfesoral por defecto si no existe
        $ayudaProfesoral = User::firstOrCreate([
            'email' => 'ayudaprofesoral@universidad.com'
        ], [
            'municipio_id'           => 703, // Cambia este valor según el municipio en tu DB
            'tipo_identificacion'    => 'Cédula de ciudadanía', // Cambia según los valores en TipoIdentificacion::all()
            'numero_identificacion'  => '910111213', // Cambia según necesidad
            'genero'                 => 'Femenino', // Cambia según los valores en Genero::all()
            'primer_nombre'          => 'Ayuda',
            'segundo_nombre'         => 'Profesoral',
            'primer_apellido'        => 'Universidad',
            'segundo_apellido'       => 'Gestión',
            'fecha_nacimiento'       => '1950-01-01', // Ajusta según necesidad
            'estado_civil'           => 'Soltero', // Cambia según los valores en EstadoCivil::all()
            'email'                  => 'ayudaprofesoral@universidad.com',
            'password'               => Hash::make('ayudaprofesoral123'), // Cambia la contraseña si lo deseas
        ]);
        $ayudaProfesoral->assignRole('Ayuda Profesoral');

        echo "Ayuda Profesoral creado con email: ayudaprofesoral@universidad.com y contraseña: ayudaprofesoral123\n";
    }
}
