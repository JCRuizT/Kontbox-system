<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seed que crea roles, permisos y asigna permisos a cada rol.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Lista completa de permisos del sistema.
     */
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'microservices.create', 'microservices.read', 'microservices.update', 'microservices.deactivate',
            'plans.create', 'plans.read', 'plans.update', 'plans.deactivate',
            'activities.create', 'activities.read', 'activities.update',
            'prospects.create', 'prospects.read', 'prospects.update',
            'quotations.create', 'quotations.read', 'quotations.update_own',
            'quotations.send_for_approval', 'quotations.approve', 'quotations.reject',
            'contracts.create', 'contracts.read', 'contracts.upload_document',
            'contracts.activate', 'contracts.anulate',
            'amendments.create', 'amendments.read',
            'invoices.create', 'invoices.read',
            'audit.read',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        $admin = Role::findOrCreate('admin');
        $vendor = Role::findOrCreate('vendor');
        $commercialManager = Role::findOrCreate('commercial_manager');
        $administrative = Role::findOrCreate('administrative');

        // Admin tiene TODOS los permisos, incluyendo firmar contratos (upload_document).
        // TODO: En futura versión, restringir firmas solo a commercial_manager.
        //       Admin debería conservar solo permisos de supervisión/configuración.
        // Admin: acceso total al sistema. Puede crear/leer/actualizar todo,
        // incluyendo configuración, contratos y auditoría.
        $admin->givePermissionTo(Permission::all());

        // Vendor (Vendedor): gestión comercial básica. Crea y edita prospectos
        // y cotizaciones propias, las envía a aprobación y consulta contratos.
        $vendor->givePermissionTo([
            'prospects.create', 'prospects.read', 'prospects.update',
            'quotations.create', 'quotations.read', 'quotations.update_own',
            'quotations.send_for_approval',
            'contracts.read',
        ]);

        // Commercial Manager (Gerente Comercial): supervisa cotizaciones
        // (aprueba/rechaza), gestiona contratos y modificaciones, y consulta auditoría.
        $commercialManager->givePermissionTo([
            'prospects.read',
            'quotations.read', 'quotations.approve', 'quotations.reject',
            'contracts.create', 'contracts.read', 'contracts.upload_document',
            'contracts.activate', 'contracts.anulate',
            'amendments.create', 'amendments.read',
            'audit.read',
        ]);

        // Administrative (Administrativo): solo consulta contratos y gestiona facturación.
        $administrative->givePermissionTo([
            'contracts.read',
            'invoices.create', 'invoices.read',
        ]);
    }
}
