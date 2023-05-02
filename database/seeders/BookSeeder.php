<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('books')->insert([
            ['user_publisher_id' => 1, 'title' => 'Teste 1'],
            ['user_publisher_id' => 1, 'title' => 'Teste 2'],
        ]);
    }
}
