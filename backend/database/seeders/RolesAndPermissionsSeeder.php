<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private const DELETE_USERS_PERMISSION = 'delete users';
    //'view any departments'
    private const VIEW_ANY_DEPARTMENTS_PERMISSION = 'view any departments';
    private const VIEW_USERS_PERMISSION = 'view users';
    private const VIEW_ANY_USERS_PERMISSION = 'view any users';
    private const UPDATE_USERS_PERMISSION = 'update users';
    private const VIEW_DEPARTMENTS_PERMISSION = 'view departments';


    public function run(): void
    {

        $permissionsByCategory = [
            'Departments' => [
                'create departments',
                'delete departments',
                'force delete departments',
                'restore departments',
                'update departments',
                self::VIEW_ANY_DEPARTMENTS_PERMISSION,
                self::VIEW_DEPARTMENTS_PERMISSION,
            ],
            'Permissions' => [
                'create permissions',
                'delete permissions',
                'force delete permissions',
                'restore permissions',
                'update permissions',
                'view any permissions',
                'view permissions',
            ],
            'Roles' => [
                'create roles',
                'delete roles',
                'force delete roles',
                'restore roles',
                'update roles',
                'view any roles',
                'view roles',
            ],
            'Users' => [
                'create users',
                'delete any users',
                self::DELETE_USERS_PERMISSION,
                'update any users',
                self::UPDATE_USERS_PERMISSION,
                self::VIEW_ANY_USERS_PERMISSION,
                self::VIEW_USERS_PERMISSION,
            ],
            'Enrollments' => [
                'create enrollments',
                'delete enrollments',
                'force delete enrollments',
                'restore enrollments',
                'update enrollments',
                'view any enrollments',
                'view enrollments',
            ],
        ];
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($permissionsByCategory as $category => $permissions) {
            foreach ($permissions as $permission) {
                Permission::create(['name' => $permission, 'category' => $category]);
            }
        }

        //Create Roles
        Role::create(['name' => 'student']);
        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'super-admin']);

        //Assign Permissions to Roles
        Role::findByName('student')->givePermissionTo([
            self::VIEW_USERS_PERMISSION,
            self::VIEW_DEPARTMENTS_PERMISSION,
            'view any departments',
            self::UPDATE_USERS_PERMISSION,
            'delete users',
        ]);
        Role::findByName('teacher')->givePermissionTo([
            self::VIEW_USERS_PERMISSION,
            'view any users',
            self::UPDATE_USERS_PERMISSION,
            self::DELETE_USERS_PERMISSION,
            'view departments',
            self::VIEW_ANY_DEPARTMENTS_PERMISSION,
        ]);
        Role::findByName('admin')->givePermissionTo([
            'create users',
            'delete any users',
            self::DELETE_USERS_PERMISSION,
            'update any users',
            self::VIEW_USERS_PERMISSION,
            self::UPDATE_USERS_PERMISSION,
            self::VIEW_ANY_USERS_PERMISSION,
            'create departments',
            'delete departments',
            'force delete departments',
            'restore departments',
            'update departments',
            self::VIEW_ANY_DEPARTMENTS_PERMISSION,
            self::VIEW_DEPARTMENTS_PERMISSION,
            //temporarily added
            'create permissions',
            'delete permissions',
            'force delete permissions',
            'restore permissions',
            'update permissions',
            'view any permissions',
            'view permissions',
            'create roles',
            'delete roles',
            'force delete roles',
            'restore roles',
            'update roles',
            'view any roles',
            'view roles',
        ]);
        Role::findByName('super-admin')->givePermissionTo(Permission::all());

    }
}
