<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckPermissions extends Command
{
    protected $signature = 'permissions:check {user_id=1}';
    protected $description = 'Check permissions for a specific user';

    public function handle()
    {
        $user = User::find($this->argument('user_id'));
        if (!$user) {
            $this->error('User not found');
            return;
        }

        $this->info("User: {$user->name}");
        $this->info("Roles: " . implode(', ', $user->roles->pluck('name')->toArray() ?: ['None']));
        $this->info("Direct Permissions: " . implode(', ', $user->permissions->pluck('name')->toArray() ?: ['None']));
        $this->info("All Permissions (via roles): " . implode(', ', $user->getAllPermissions()->pluck('name')->toArray() ?: ['None']));

        $this->info("\nTesting Gates:");
        $this->testGate($user, 'contacts.view');
        $this->testGate($user, 'contacts.create');
        $this->testGate($user, 'contacts.edit');
        $this->testGate($user, 'permissions.view');
    }

    private function testGate($user, $ability)
    {
        try {
            \Illuminate\Support\Facades\Gate::forUser($user)->authorize($ability);
            $this->line("  ✓ {$ability} - ALLOWED");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->line("  ✗ {$ability} - DENIED");
        }
    }
}
