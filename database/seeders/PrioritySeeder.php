<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $priorities = [
            [
                'priority_name' => 'Major',
                'created_at' => now(),
            ],
            [
                'priority_name' => 'Block',
                'created_at' => now(),
            ],
            [
                'priority_name' => 'Critical',
                'created_at' => now(),
            ],
            [
                'priority_name' => 'Minor',
                'created_at' => now(),
            ],
            [
                'priority_name' => 'trivial',
                'created_at' => now(),
            ],
            [
                'priority_name' => 'Normal',
                'created_at' => now(),
            ],
        ];
        

        DB::table('priority')->insert($priorities);
    }
}
