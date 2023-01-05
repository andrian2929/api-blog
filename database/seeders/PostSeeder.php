<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('posts')->insert([
            'user_id' => 1,
            'title' => 'Saya makan sate',
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quae.',
        ]);

        DB::table('posts')->insert([
            'user_id' => 2,
            'title' => 'Pergi ke bioskop',
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quae.',
        ]);

        DB::table('posts')->insert([
            'user_id' => 2,
            'title' => 'Pergi ke tempat sana',
            'body' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quae.',
        ]);
    }
}
