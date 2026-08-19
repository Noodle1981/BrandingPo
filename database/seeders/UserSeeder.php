<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Administrador (Control Total)
        User::updateOrCreate(
            ['email' => 'admin@brandingpo.com'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Consultor (Operativo, Carga Fast-Flow, Pauta y Análisis)
        User::updateOrCreate(
            ['email' => 'consultor@brandingpo.com'],
            [
                'name' => 'Consultor Estratégico',
                'password' => Hash::make('password'),
                'role' => 'consultor',
            ]
        );

        // 3. Visualizador (Solo Lectura - Sin botones de mutación)
        User::updateOrCreate(
            ['email' => 'visualizador@brandingpo.com'],
            [
                'name' => 'Visualizador Ejecutivo',
                'password' => Hash::make('password'),
                'role' => 'visualizador',
            ]
        );
    }
}
