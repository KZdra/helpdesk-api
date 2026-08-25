<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlaPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $policies = [
            [
                'priority_name' => 'Critical',
                'response_time_minutes' => 15,    // 15 menit respon
                'resolution_time_minutes' => 120, // 2 jam penyelesaian
                'is_active' => true,
            ],
            [
                'priority_name' => 'Block',
                'response_time_minutes' => 20,
                'resolution_time_minutes' => 180, // 3 jam penyelesaian
                'is_active' => true,
            ],
            [
                'priority_name' => 'Major',
                'response_time_minutes' => 30,    // 30 menit respon
                'resolution_time_minutes' => 240, // 4 jam penyelesaian
                'is_active' => true,
            ],
            [
                'priority_name' => 'Normal',
                'response_time_minutes' => 60,    // 1 jam respon
                'resolution_time_minutes' => 480, // 8 jam penyelesaian (1 hari kerja)
                'is_active' => true,
            ],
            [
                'priority_name' => 'Minor',
                'response_time_minutes' => 120,   // 2 jam respon
                'resolution_time_minutes' => 1440, // 24 jam penyelesaian
                'is_active' => true,
            ],
            [
                'priority_name' => 'trivial',
                'response_time_minutes' => 240,   // 4 jam respon
                'resolution_time_minutes' => 2880, // 48 jam penyelesaian
                'is_active' => true,
            ],
        ];

        foreach ($policies as $policy) {
            $priority = DB::table('priority')
                ->where('priority_name', 'like', $policy['priority_name'])
                ->first();

            $policyData = [
                'priority_id' => $priority ? $priority->id : null,
                'priority_name' => $policy['priority_name'],
                'response_time_minutes' => $policy['response_time_minutes'],
                'resolution_time_minutes' => $policy['resolution_time_minutes'],
                'is_active' => $policy['is_active'],
                'updated_at' => now(),
            ];

            $existing = DB::table('sla_policies')->where('priority_name', $policy['priority_name'])->first();
            if ($existing) {
                DB::table('sla_policies')->where('id', $existing->id)->update($policyData);
            } else {
                $policyData['created_at'] = now();
                DB::table('sla_policies')->insert($policyData);
            }
        }
    }
}
