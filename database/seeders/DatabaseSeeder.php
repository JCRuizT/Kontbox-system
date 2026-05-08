<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Ejecuta los seeders en orden: primero roles/permisos, luego usuarios de prueba,
    // luego datos semilla de microservicios, actividades y planes contables
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CreateUsersSeeder::class,
            AccountingSeedDataSeeder::class,
        ]);
    }
}
