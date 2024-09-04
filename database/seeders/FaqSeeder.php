<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faqs = [
            [
                'question' => 'Apa itu Sistem Ticketing?',
                'answer' => 'Sistem Ticketing adalah sebuah platform yang digunakan untuk mengelola, melacak, dan menyelesaikan permintaan atau masalah yang diajukan oleh pengguna atau pelanggan. Setiap permintaan dibuat dalam bentuk \'ticket\' yang kemudian ditangani oleh tim terkait.'
            ],
            [
                'question' => 'Bagaimana cara membuat ticket baru?',
                'answer' => 'Untuk membuat ticket baru, pengguna perlu login ke dalam sistem, mengisi formulir yang disediakan, dan menyertakan detail masalah atau permintaan. Setelah formulir diisi, ticket akan dikirim ke tim yang bertanggung jawab untuk diproses.'
            ],
            [
                'question' => 'Apa yang harus dilakukan setelah ticket dibuat?',
                'answer' => 'Setelah ticket dibuat, sistem akan memberikan nomor ticket yang bisa digunakan untuk melacak status dari permintaan tersebut. Pengguna dapat memantau status ticket melalui dashboard atau notifikasi yang dikirim oleh sistem.'
            ],
            [
                'question' => 'Berapa lama waktu yang dibutuhkan untuk menyelesaikan ticket?',
                'answer' => 'Waktu penyelesaian ticket bervariasi tergantung pada kompleksitas masalah dan ketersediaan tim. Sistem biasanya menyediakan estimasi waktu penyelesaian setelah ticket dibuat.'
            ],
            [
                'question' => 'Bagaimana cara menambahkan lampiran ke dalam ticket?',
                'answer' => 'Ketika membuat atau memperbarui ticket, Anda dapat menambahkan lampiran seperti gambar atau dokumen yang relevan dengan masalah yang dilaporkan. Pastikan lampiran tersebut tidak melebihi batas ukuran file yang ditentukan oleh sistem.'
            ],
            [
                'question' => 'Apakah saya dapat mengedit ticket yang sudah dibuat?',
                'answer' => 'Anda dapat mengedit detail ticket selama ticket tersebut belum ditangani atau belum dalam status \'sedang diproses\'. Jika ticket sudah dalam proses, Anda mungkin perlu menghubungi tim support untuk melakukan perubahan.'
            ],
            [
                'question' => 'Bagaimana cara menutup ticket?',
                'answer' => 'Setelah masalah atau permintaan Anda ditangani dan Anda puas dengan solusinya, Anda dapat menutup ticket melalui dashboard. Jika tidak puas, Anda juga dapat meminta revisi atau pembukaan kembali ticket.'
            ],
            [
                'question' => 'Apa yang harus dilakukan jika tidak ada tanggapan pada ticket?',
                'answer' => 'Jika ticket Anda tidak mendapat tanggapan dalam waktu yang wajar, Anda bisa menghubungi tim support langsung melalui kontak yang disediakan atau mengirim pengingat melalui sistem ticketing.'
            ],
            [
                'question' => 'Apa itu prioritas tiket dalam sistem ticketing?',
                'answer' => 'Prioritas tiket menentukan tingkat urgensi atau kepentingan dari setiap tiket atau masalah yang dilaporkan. Ini membantu tim untuk mengidentifikasi dan menangani masalah yang paling mendesak terlebih dahulu.'
            ],
            [
                'question' => 'Apa perbedaan antara prioritas Critical, Major, Normal, Minor, dan Trivial?',
                'answer' => 'Prioritas "Critical" adalah untuk masalah yang sangat mendesak yang mempengaruhi seluruh sistem. "Major" untuk masalah serius yang mempengaruhi sebagian besar pengguna. "Normal" untuk masalah yang perlu diperbaiki tetapi tidak mendesak. "Minor" untuk masalah kecil atau bug yang tidak signifikan, dan "Trivial" untuk masalah yang sangat kecil dan tidak berdampak besar pada sistem.'
            ],
            [
                'question' => 'Kapan saya harus menggunakan prioritas Critical untuk tiket saya?',
                'answer' => 'Gunakan prioritas "Critical" ketika masalah menyebabkan sistem tidak bisa digunakan, ada bug keamanan serius, atau ketika masalah tersebut berdampak besar pada semua pengguna.'
            ],
            [
                'question' => 'Bagaimana cara menentukan prioritas yang tepat untuk tiket saya?',
                'answer' => 'Pertimbangkan dampak dan urgensi dari masalah yang Anda hadapi. Jika masalah mempengaruhi seluruh sistem atau mayoritas pengguna, pilih "Critical" atau "Major". Jika tidak terlalu mendesak, pilih "Normal", "Minor", atau "Trivial".'
            ],
            [
                'question' => 'Apa yang dimaksud dengan prioritas Block dalam sistem ticketing?',
                'answer' => 'Prioritas "Block" digunakan ketika pekerjaan tidak dapat dilanjutkan karena ada masalah lain yang menghambat, misalnya menunggu penyelesaian tiket lain atau masalah eksternal yang harus diselesaikan terlebih dahulu.'
            ],
            [
                'question' => 'Apakah saya bisa mengubah prioritas tiket setelah diajukan?',
                'answer' => 'Ya, Anda dapat mengubah prioritas tiket setelah diajukan. Silakan hubungi tim dukungan atau ubah melalui portal tiket jika fitur ini tersedia.'
            ],
            [
                'question' => 'Mengapa tiket saya diberi prioritas Trivial?',
                'answer' => 'Tiket Anda mungkin dianggap tidak mendesak atau masalahnya kecil yang tidak mempengaruhi fungsi utama sistem. Jika Anda merasa ini salah, Anda dapat meminta peninjauan ulang prioritas tiket Anda.'
            ],
            [
                'question' => 'Bagaimana prioritas tiket mempengaruhi waktu penyelesaian masalah?',
                'answer' => 'Tiket dengan prioritas lebih tinggi seperti "Critical" atau "Major" akan ditangani terlebih dahulu, sedangkan tiket dengan prioritas lebih rendah seperti "Minor" atau "Trivial" mungkin memakan waktu lebih lama untuk diselesaikan.'
            ]
        ];

        DB::table('faqs')->insert($faqs);
    }
}
