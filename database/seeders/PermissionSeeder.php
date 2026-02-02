<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions with groups
        $permissions = [
            // User Management
            ['name' => 'users.view', 'group' => 'Users'],
            ['name' => 'users.create', 'group' => 'Users'],
            ['name' => 'users.edit', 'group' => 'Users'],
            ['name' => 'users.delete', 'group' => 'Users'],

            // Contact Management
            ['name' => 'contacts.view', 'group' => 'Contacts'],
            ['name' => 'contacts.create', 'group' => 'Contacts'],
            ['name' => 'contacts.edit', 'group' => 'Contacts'],
            ['name' => 'contacts.delete', 'group' => 'Contacts'],
            ['name' => 'contacts.import', 'group' => 'Contacts'],
            ['name' => 'contacts.export', 'group' => 'Contacts'],

            // Product Management
            ['name' => 'products.view', 'group' => 'Products'],
            ['name' => 'products.create', 'group' => 'Products'],
            ['name' => 'products.edit', 'group' => 'Products'],
            ['name' => 'products.delete', 'group' => 'Products'],

            // Order Management
            ['name' => 'orders.view', 'group' => 'Orders'],
            ['name' => 'orders.create', 'group' => 'Orders'],
            ['name' => 'orders.edit', 'group' => 'Orders'],
            ['name' => 'orders.delete', 'group' => 'Orders'],

            // Queue Management
            ['name' => 'queue.view', 'group' => 'Queue'],
            ['name' => 'queue.retry', 'group' => 'Queue'],
            ['name' => 'queue.delete', 'group' => 'Queue'],

            // Access Control
            ['name' => 'permissions.view', 'group' => 'Access Control'],
            ['name' => 'permissions.create', 'group' => 'Access Control'],
            ['name' => 'permissions.edit', 'group' => 'Access Control'],
            ['name' => 'permissions.delete', 'group' => 'Access Control'],
            ['name' => 'roles.view', 'group' => 'Access Control'],
            ['name' => 'roles.create', 'group' => 'Access Control'],
            ['name' => 'roles.edit', 'group' => 'Access Control'],
            ['name' => 'roles.delete', 'group' => 'Access Control'],
            ['name' => 'user-roles.view', 'group' => 'Access Control'],
            ['name' => 'user-roles.assign', 'group' => 'Access Control'],

            // Settings
            ['name' => 'settings.view', 'group' => 'Settings'],
            ['name' => 'settings.edit', 'group' => 'Settings'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['group' => $permission['group']]
            );
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Assign all permissions to super-admin
        $superAdmin->syncPermissions(Permission::all());

        // Assign permissions to admin (all except access control management)
        $adminPermissions = Permission::whereNotIn('name', [
            'permissions.create', 'permissions.edit', 'permissions.delete',
            'roles.create', 'roles.edit', 'roles.delete',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Assign permissions to editor
        $editorPermissions = Permission::whereIn('name', [
            'users.view',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'products.view', 'products.create', 'products.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'queue.view',
        ])->get();
        $editor->syncPermissions($editorPermissions);

        // Assign permissions to viewer (view only)
        $viewerPermissions = Permission::where('name', 'like', '%.view')->get();
        $viewer->syncPermissions($viewerPermissions);

        $this->command->info('Permissions and Roles seeded successfully!');
    }
}
