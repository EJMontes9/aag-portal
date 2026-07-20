<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Reparto de permisos por rol.
 *
 * CONTEXTO DE SEGURIDAD -- Hasta ahora los cuatro roles existian pero SIN un
 * solo permiso, y eso no impedia nada: Filament, al no encontrar una Policy
 * para un recurso, permitia por defecto. Es decir, un "editor" podia hacer de
 * todo, incluidos los datos personales de suscriptores y los envios de
 * formularios de contacto.
 *
 * Ahora que la ausencia de policy DENIEGA (ver AdminPanelProvider::boot), hay
 * que decir explicitamente que puede hacer cada rol. Este seeder es esa
 * declaracion, y esta versionado para que el reparto se pueda reproducir en
 * el servidor sin tener que repetirlo a mano.
 *
 *   php artisan db:seed --class=RolePermissionSeeder
 *
 * Es idempotente: se puede volver a ejecutar tras un shield:generate para
 * incorporar los permisos de recursos nuevos.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Recursos con datos PERSONALES. Solo los administradores los ven.
     * Los suscriptores guardan email + IP + user agent; los envios de
     * formulario, todo lo que haya escrito el ciudadano.
     */
    protected array $recursosConDatosPersonales = [
        'subscriber',
        'form::submission',
    ];

    /**
     * Recursos de configuracion y seguridad. Solo super_admin.
     */
    protected array $recursosCriticos = [
        'role',
        'shield::role',
    ];

    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $todos = Permission::pluck('name')->all();

        if (empty($todos)) {
            $this->command?->error('No hay permisos en la base de datos.');
            $this->command?->error('Ejecuta primero: php artisan shield:generate --all --option=policies_and_permissions --panel=admin');
            return;
        }

        // ── super_admin: todo ────────────────────────────────────────────────
        // Ademas, AppServiceProvider tiene un Gate::before que le da acceso
        // total; esto es para que la interfaz de Shield lo muestre coherente.
        $this->asignar('super_admin', $todos);

        // ── admin: todo salvo la gestion de roles ────────────────────────────
        // Se le niega tocar roles y permisos para que no pueda auto-elevarse
        // ni crear un rol con mas privilegios de los que tiene.
        $admin = array_filter($todos, fn ($p) => ! $this->perteneceA($p, $this->recursosCriticos));
        $this->asignar('admin', $admin);

        // ── publisher: contenido completo, sin datos personales ni config ────
        $publisher = array_filter($todos, function ($p) {
            if ($this->perteneceA($p, $this->recursosCriticos)) return false;
            if ($this->perteneceA($p, $this->recursosConDatosPersonales)) return false;
            if (str_contains($p, 'site::settings')) return false;
            return true;
        });
        $this->asignar('publisher', $publisher);

        // ── editor: como publisher, pero sin borrar nada ─────────────────────
        // Puede redactar y actualizar; no elimina contenido publicado ni
        // accede al registro de actividad.
        $editor = array_filter($publisher, function ($p) {
            if (str_starts_with($p, 'delete_')) return false;
            if (str_starts_with($p, 'force_delete_')) return false;
            if (str_starts_with($p, 'restore_')) return false;
            if (str_contains($p, 'activity::log')) return false;
            return true;
        });
        $this->asignar('editor', $editor);

        app()['cache']->forget('spatie.permission.cache');

        $this->command?->newLine();
        foreach (['super_admin', 'admin', 'publisher', 'editor'] as $rol) {
            $n = Role::where('name', $rol)->first()?->permissions()->count() ?? 0;
            $this->command?->info(sprintf('  %-12s %3d permisos', $rol, $n));
        }
    }

    /**
     * ¿El permiso pertenece a alguno de estos recursos?
     *
     * Shield nombra los permisos como "accion_recurso", conservando los dos
     * puntos del recurso: view_any_subscriber, view_any_form::submission.
     * OJO: aqui NO se pueden normalizar los "::" a "_", porque entonces
     * "form::submission" deja de coincidir con "view_any_form::submission" y
     * el filtro no descarta nada (era el fallo de la primera version, que
     * dejaba al editor con acceso a los envios de formularios).
     */
    protected function perteneceA(string $permiso, array $recursos): bool
    {
        foreach ($recursos as $recurso) {
            if (str_ends_with($permiso, '_' . $recurso) || $permiso === $recurso) {
                return true;
            }
        }

        return false;
    }

    protected function asignar(string $rol, array $permisos): void
    {
        $role = Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        $role->syncPermissions(array_values($permisos));
    }
}
