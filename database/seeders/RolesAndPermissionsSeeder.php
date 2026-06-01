<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Landlord management (super_admin only)
            'view landlords',
            'create landlords',
            'update landlords',
            'delete landlords',

            // Properties
            'view properties',
            'create properties',
            'update properties',
            'delete properties',

            // Units
            'view units',
            'create units',
            'update units',
            'delete units',

            // Caretakers
            'view caretakers',
            'create caretakers',
            'update caretakers',
            'delete caretakers',

            // Tenants
            'view tenants',
            'create tenants',
            'update tenants',
            'delete tenants',

            // Leases
            'view leases',
            'create leases',
            'update leases',
            'delete leases',

            // Deposits & Escrow
            'view deposits',
            'create deposits',
            'update deposits',
            'initiate deposit collection',
            'initiate deposit refund',

            // Deposit Deductions
            'view deposit deductions',
            'create deposit deductions',
            'update deposit deductions',
            'approve deposit deductions',
            'reject deposit deductions',

            // Inspection Reports
            'view inspections',
            'create inspections',
            'update inspections',
            'complete inspections',

            // Maintenance Requests
            'view maintenance',
            'create maintenance',
            'update maintenance status',

            // Messages
            'view messages',
            'send messages',

            // Reports
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // --- Roles ---
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $landlord = Role::firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        $caretaker = Role::firstOrCreate(['name' => 'caretaker', 'guard_name' => 'web']);
        $tenant = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);

        // Super admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Landlord permissions
        $landlord->syncPermissions([
            'view properties', 'view units',
            'view caretakers', 'create caretakers', 'update caretakers', 'delete caretakers',
            'view tenants',
            'view leases',
            'view deposits', 'initiate deposit refund',
            'view deposit deductions', 'approve deposit deductions', 'reject deposit deductions',
            'view inspections',
            'view maintenance',
            'view messages', 'send messages',
            'view reports',
        ]);

        // Caretaker permissions
        $caretaker->syncPermissions([
            'view properties', 'view units',
            'view tenants', 'create tenants', 'update tenants',
            'view leases', 'create leases', 'update leases',
            'view deposits', 'create deposits', 'update deposits', 'initiate deposit collection',
            'view deposit deductions', 'create deposit deductions', 'update deposit deductions',
            'view inspections', 'create inspections', 'update inspections', 'complete inspections',
            'view maintenance', 'create maintenance', 'update maintenance status',
            'view messages', 'send messages',
        ]);

        // Tenant permissions (used for API gate checks)
        $tenant->syncPermissions([
            'view deposits',
            'view inspections',
            'view maintenance', 'create maintenance',
            'view messages', 'send messages',
        ]);

        // --- Default Super Admin user ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@tdaps.co.ke'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@tdaps.co.ke',
                'phone' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['super_admin']);

        $this->command->info('Roles, permissions, and super admin seeded successfully.');
    }
}
