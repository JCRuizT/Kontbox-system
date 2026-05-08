<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed que crea usuarios predefinidos para desarrollo y testing.
 * Cada usuario se asigna a un rol específico para probar la segmentación de acceso.
 */
class CreateUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Julio Ruiz', 'email' => 'juliocruizt@gmail.com', 'password' => 'Test54321@', 'role' => 'admin'],
            ['name' => 'Carlos Méndez', 'email' => 'gerente@kontbox.com', 'password' => 'Kontbox2026*', 'role' => 'commercial_manager'],
            ['name' => 'María García', 'email' => 'vendedor@kontbox.com', 'password' => 'Kontbox2026*', 'role' => 'vendor'],
            ['name' => 'Ana López', 'email' => 'administrativo@kontbox.com', 'password' => 'Kontbox2026*', 'role' => 'administrative'],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }

            $this->command->info("Usuario {$data['email']} listo con rol: {$role}");
        }
    }
}
