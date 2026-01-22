<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class SeedPermissions extends Command
{
    protected $signature = 'permissions:seed';
    protected $description = 'Seed all permissions and roles';

    public function handle()
    {
        $this->info('Seeding permissions and roles...');

        try {
            (new PermissionSeeder())->run();
            $this->info('✓ Permissions and roles seeded successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
