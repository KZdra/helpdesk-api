<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@x.com',
                'password' => Hash::make('admin'), 
                'role_id' => 1,
                'created_at' => now(),
          ],
            [
                'name' => 'Support User',
                'email' => 'support@x.com',
                'password' => Hash::make('support'),
                'role_id' => 2,
                'created_at' => now(),
          ],
            [
                'name' => 'Client User',
                'email' => 'client@x.com',
                'password' => Hash::make('client'),
                'role_id' => 3,
                'created_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
