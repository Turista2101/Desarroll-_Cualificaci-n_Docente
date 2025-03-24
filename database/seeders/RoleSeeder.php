<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los roles a crear
        $roles = ['Administrador', 'Aspirante', 'Docente', 'Talento Humano', 'Ayuda Profesoral'];

        // Crear los roles si no existen
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
