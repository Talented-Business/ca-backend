<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $super = Role::create(['name' => 'super']);
        $permission = Permission::create(['name' => 'edit employee']);
        $super->givePermissionTo($permission);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'read department']);
        $super->givePermissionTo($permission);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'edit department']);
        $super->givePermissionTo($permission);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'read attribute']);
        $super->givePermissionTo($permission);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'edit attribute']);
        $super->givePermissionTo($permission);
        $permission->assignRole($super);
        $admin = Role::create(['name' => 'admin']);
        $company = Role::create(['name' => 'company']);
        $member = Role::create(['name' => 'member']);
        $employee = Role::create(['name' => 'employee']);
        $permission = Permission::create(['name' => 'read company']);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'delete company']);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'add company']);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'edit company']);
        $permission->assignRole($super);
        $permission = Permission::create(['name' => 'approve/reject job profile']);
        $permission = Permission::create(['name' => 'edit timeoff']);
        $permission = Permission::create(['name' => 'approve/reject timeoff']);
        $permission = Permission::create(['name' => 'create contract']);
        $permission = Permission::create(['name' => 'edit job profile']);
        $permission = Permission::create(['name' => 'edit commission']);
        $permission = Permission::create(['name' => 'edit invoice']);
        $permission = Permission::create(['name' => 'approve/reject invoice']);
    }
}
