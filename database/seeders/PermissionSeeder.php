<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\PermissionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermissionService::createAllPermissions();
        PermissionService::createRoles();
    }
}
