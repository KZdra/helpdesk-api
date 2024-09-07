<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kategoris = [
            [
                'nama_kategori' => 'IT Support',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Permintaan Fitur',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Masalah Akun',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Jaringan dan Koneksi',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Perangkat Keras',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Perangkat Lunak',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Permintaan Layanan',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Masalah Email',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Masalah Keamanan',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Masalah Website',
                'status' => 'active'
            ],
            [
                'nama_kategori' => 'Administrasi',
                'status' => 'active'
            ]
        ];
    DB::table('kategoris')->insert($kategoris)   ;     
    }
}
