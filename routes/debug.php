<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Route::get('/debug/permissions', function () {
    // Cek semua permissions
    $permissions = Permission::all();
    echo "<h3>Permissions di database (" . $permissions->count() . "):</h3>";
    if ($permissions->isEmpty()) {
        echo "<span style='color:red'>KOSONG! Jalankan: php artisan db:seed --class=PermissionSeeder</span><br>";
    } else {
        foreach ($permissions as $p) {
            echo "- {$p->name}<br>";
        }
    }

    echo "<hr>";

    // Cek roles
    $roles = Role::with('permissions')->get();
    echo "<h3>Roles di database (" . $roles->count() . "):</h3>";
    foreach ($roles as $r) {
        $perms = $r->permissions->pluck('name')->toArray();
        echo "- {$r->name} (" . count($perms) . " permissions)<br>";
    }

    echo "<hr>";

    // Cek current user
    $user = auth()->user();
    if ($user) {
        echo "<h3>Current User:</h3>";
        echo "ID: {$user->id}<br>";
        echo "Name: {$user->name}<br>";
        echo "Email: {$user->email}<br>";
        echo "Roles: " . ($user->roles->isEmpty() ? '<span style="color:red">TIDAK ADA ROLE!</span>' : implode(', ', $user->roles->pluck('name')->toArray())) . "<br>";

        // Permissions dari role
        $allPerms = $user->getAllPermissions();
        echo "All Permissions (via role): " . ($allPerms->isEmpty() ? '<span style="color:red">TIDAK ADA PERMISSION!</span>' : implode(', ', $allPerms->pluck('name')->toArray())) . "<br>";

        echo "<hr>";
        echo "<h3>Test Permission Check:</h3>";

        // Test dengan checkPermissionTo (tidak throw exception)
        $testPerms = ['contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete'];
        foreach ($testPerms as $perm) {
            $hasIt = $user->checkPermissionTo($perm);
            $icon = $hasIt ? '✓' : '✗';
            $color = $hasIt ? 'green' : 'red';
            echo "<span style='color:{$color}'>{$icon} {$perm}</span><br>";
        }

        echo "<hr>";
        echo "<h3>Test Gate::allows():</h3>";
        foreach ($testPerms as $perm) {
            $allowed = Gate::allows($perm);
            $icon = $allowed ? '✓' : '✗';
            $color = $allowed ? 'green' : 'red';
            echo "<span style='color:{$color}'>{$icon} Gate::allows('{$perm}')</span><br>";
        }
    } else {
        echo "<span style='color:red'>Tidak ada user yang login!</span>";
    }
});
