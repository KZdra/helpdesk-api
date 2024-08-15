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
        DB::table("users")->insert([
            "name"=> "Admin",
            "email"=> "admin@x.com",
            "role" => "admin",
            "password"=> Hash::make("admin"),
        
        ]);
        DB::table("users")->insert([
            "name"=> "Client",
            "email"=> "client@x.com",
            "role" => "client",
            "password"=> Hash::make("client"),
        
        ]);
        DB::table("users")->insert([
            "name"=> "Support",
            "email"=> "support@x.com",
            "role" => "support",
            "password"=> Hash::make("support"),
        
        ]);
    }
}
