<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Services\PermissionService;
use Spatie\Permission\Models\Permission;

class SetupPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:permissions { --fresh : Create fresh permissions }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup permissions for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Clear Existing Roles and Permissions');

            Permission::query()->delete();
            $this->info('Existing permissions cleared.');

            Role::query()->delete();
            $this->info('Existing roles cleared.');

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        PermissionService::createAllPermissions();
        $this->info('Permissions created successfully.');

        PermissionService::createRoles();
        $this->info('Roles created successfully.');


        $this->info('Setup completed successfully.');
    }
}
