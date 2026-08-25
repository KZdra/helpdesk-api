<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            [
                'name' => 'Dinas Komunikasi dan Informatika (Diskominfo)',
                'code' => 'DISKOMINFO',
                'description' => 'Unit Pelayanan IT, Jaringan, dan Sistem Informasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Badan Kepegawaian dan Pengembangan SDM (BKPSDM)',
                'code' => 'BKPSDM',
                'description' => 'Unit Layanan Kepegawaian & Administrasi ASN',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Badan Pengelolaan Keuangan dan Aset Daerah (BPKAD)',
                'code' => 'BPKAD',
                'description' => 'Unit Pengelolaan Anggaran, Perbendaharaan, dan Aset',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sekretariat Daerah (SETDA)',
                'code' => 'SETDA',
                'description' => 'Bagian Umum, Protokol, dan Tata Usaha Pimpinan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inspektorat Daerah',
                'code' => 'INSPEKTORAT',
                'description' => 'Pengawasan Internal dan Kepatuhan Tata Kelola',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
