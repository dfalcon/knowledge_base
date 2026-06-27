<?php

namespace Database\Seeders;

use App\Modules\Users\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'member']);
        Role::firstOrCreate(['name' => 'admin']);

        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin',
                'password' => 'demo1234',
                'status'   => 'active',
            ]
        );

        $user->syncRoles('admin');
    }
}
