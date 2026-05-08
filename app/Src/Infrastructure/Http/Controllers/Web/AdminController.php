<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Src\Domain\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Controlador de administración del sistema.
 * Gestiona usuarios, roles/permisos y visualización de logs de auditoría.
 */
class AdminController extends Controller
{
    // ==================== USUARIOS ====================

    /**
     * Gestiona usuarios del sistema. Lista paginada con roles.
     */
    public function users(): View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->orderByDesc('created_at')->paginate(config('kontbox.items_per_page')),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function usersCreate(): View
    {
        return view('admin.users.form', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Almacena un nuevo usuario, asigna rol y registra auditoría.
     */
    public function usersStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        $user->assignRole($validated['role']);

        AuditService::logCreate($user, 'Usuario', ['name' => $user->name, 'email' => $user->email, 'role' => $validated['role']]);
        return to_route('admin.users')->with('success', __('domain.admin.user_created'));
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function usersEdit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Actualiza datos de usuario, rol y contraseña (opcional).
     */
    public function usersUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,name',
            'is_active' => 'nullable|boolean',
        ]);

        $original = $user->getOriginal();
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
            $user->save();
        }
        $user->syncRoles([$validated['role']]);

        AuditService::logUpdate($user, 'Usuario', $original, $user->getChanges());
        return to_route('admin.users')->with('success', __('domain.admin.user_updated'));
    }

    /**
     * Seguridad: no permite eliminar si tiene interacciones.
     * Elimina físicamente solo si no tiene activity logs, prospectos, cotizaciones ni facturas.
     * En caso contrario, sugiere deshabilitar la cuenta en lugar de eliminar.
     */
    public function usersDelete(User $user): RedirectResponse
    {
        // Validar que el usuario no tenga interacciones en el sistema
        if ($user->id === auth()->id()) {
            return back()->with('error', __('domain.admin.cannot_delete_self'));
        }

        $hasInteractions = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)->exists()
            || $user->has('prospects')->exists()
            || $user->has('quotations')->exists()
            || $user->has('invoices')->exists();

        if ($hasInteractions) {
            return back()->with('error', __('domain.admin.cannot_delete_with_interactions'));
        }

        AuditService::logDelete($user, 'Usuario');
        $user->delete();

        return to_route('admin.users')->with('success', __('domain.admin.user_deleted'));
    }

    /**
     * Restaura un usuario eliminado (soft delete).
     */
    public function usersRestore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        AuditService::log("Restauró usuario #{$id}", $user, ['action' => 'restore']);
        return to_route('admin.users')->with('success', __('domain.admin.user_restored'));
    }

    // ==================== ROLES Y PERMISOS ====================

    /**
     * Gestiona roles y permisos. Lista todos los roles con sus permisos asignados.
     */
    public function roles(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
        ]);
    }

    /**
     * Muestra el formulario de edición con permisos agrupados por módulo.
     */
    public function rolesEdit(Role $role): View
    {
        $grouped = [];
        foreach (Permission::all() as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'general';
            $action = $parts[1] ?? $perm->name;
            if (!isset($grouped[$module])) $grouped[$module] = [];
            $grouped[$module][] = ['name' => $perm->name, 'action' => $action];
        }

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'groupedPermissions' => $grouped,
            'rolePermissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    /**
     * Actualiza permisos del rol.
     * Sincroniza los permisos seleccionados y registra el cambio en auditoría.
     */
    public function rolesUpdate(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        AuditService::log("Actualizó permisos del rol {$role->name}", $role, [
            'action' => 'permissions_update',
            'role' => $role->name,
            'permissions_count' => count($validated['permissions'] ?? []),
        ]);

        return to_route('admin.roles')->with('success', __('domain.admin.role_permissions_updated', ['name' => $role->name]));
    }

    //
}
