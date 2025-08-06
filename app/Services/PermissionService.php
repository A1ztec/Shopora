<?php


namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionService
{


    public static function getResourcePermissions(): array
    {
        return [
            'user' => [
                'label' => 'User Management',
                'actions' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any']
            ],
            'role' => [
                'label' => 'Role Management',
                'actions' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any']
            ],
            'permission' => [
                'label' => 'Permission Management',
                'actions' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any']
            ],

            'category' => [
                'label' => 'Category Management',
                'actions' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any']
            ],
            'setting' => [
                'label' => 'System Settings',
                'actions' => ['view', 'update']
            ]
        ];
    }


    public static function getSystemPermissions(): array
    {
        return [
            'access_admin_panel' => 'Access Admin Panel',
            'view_dashboard' => 'View Dashboard',
            'manage_backups' => 'Manage Backups',
            'view_logs' => 'View System Logs',
            'export_data' => 'Export Data',
            'import_data' => 'Import Data',
        ];
    }


    public static function generatePermissionName(string $resource, string $action): string
    {
        return "{$action}_{$resource}";
    }

    public static function getResourcePermissionNames(string $resource): array
    {
        $configResource = self::getResourcePermissions()[$resource] ?? null;

        if (!$configResource) {
            return [];
        }

        return collect($configResource['actions'])
            ->map(fn($action) => self::generatePermissionName($resource, $action))
            ->toArray();
    }


    public static function createAllPermissions(): void
    {
        $permissions = [];

        foreach (self::getResourcePermissions() as $resource => $config) {
            foreach ($config['actions'] as $action) {
                $permissions[] = self::generatePermissionName($resource, $action);
            }
        }
        $permissions = collect($permissions);
        Permission::insert(
            $permissions->map(fn($name) => ['name' => $name, 'guard_name' => 'web'])->toArray()
        );

        foreach (self::getSystemPermissions() as $name => $label) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }

    public  static function createRoles(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(Permission::all());


        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            'access_admin_panel',
            'view_dashboard',

            self::getResourcePermissionNames('user'),

            self::getResourcePermissionNames('post'),

            self::getResourcePermissionNames('category'),

        ];
        $admin->givePermissionTo($adminPermissions);
    }
}
