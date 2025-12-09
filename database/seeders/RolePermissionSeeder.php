<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*-------------------------------------------------------------------*/
        /*------------------------- Create Permissions ----------------------*/
        /*-------------------------------------------------------------------*/
        $guard = 'api';
        $permissions = [
            //task crud
            'create.task',
            'read.task',
            'update.task',
            'delete.task',

            //get tasks
            'all.tasks',
            'my.tasks',

            //update task status
            'update.task.status',

            //task users
            'assign.task.user',
            'unassign.task.user',
            'task.users',
        ];
        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                ['name' => $name, 'guard_name' => $guard]
            );
        }

        /*-------------------------------------------------------------------*/
        /*--------------------------- Create Roles --------------------------*/
        /*-------------------------------------------------------------------*/
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);


        /*-------------------------------------------------------------------*/
        /*-------------------------- Assign Permissions ---------------------*/
        /*-------------------------------------------------------------------*/
        $userPermNames = [
            'create.task',
            'read.task',
            'update.task',
            'delete.task',
            'my.tasks',
            'update.task.status',
            'assign.task.user',
            'unassign.task.user',
            'task.users',
        ];
        $adminPermNames = array_merge($userPermNames, ['all.tasks']);

        $userPerms = Permission::whereIn('name', $userPermNames)
            ->where('guard_name', $guard)
            ->get();

        $adminPerms = Permission::whereIn('name', $adminPermNames)
            ->where('guard_name', $guard)
            ->get();

        $userRole->givePermissionTo($userPerms);
        $adminRole->givePermissionTo($adminPerms);
    }



}
