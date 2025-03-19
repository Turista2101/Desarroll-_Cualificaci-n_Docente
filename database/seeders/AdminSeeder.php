<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {

        // Crear usuario administrador por defecto si no existe
        $admin = User::firstOrCreate([
            'email' => 'admin@universidad.com'
        ], [
            'tipo_identificacion' => 'CÉDULA DE CIUDADANÍA',
            'numero_identificacion' => '123456789',
            'primer_nombre' => 'Admin',
            'segundo_nombre' => 'Sistema',
            'primer_apellido' => 'Universidad',
            'segundo_apellido' => 'Gestión',
            'email' => 'admin@universidad.com',
            'password' => Hash::make('admin123'), // Cambia la contraseña si lo deseas
        ]);

        // Asignar el rol de admin
        $admin->assignRole('admin');
        
        echo "✅ Administrador creado con email: admin@universidad.com y contraseña: admin123\n";
    }
}

