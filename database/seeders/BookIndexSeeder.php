<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookIndexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('book_indices')->insert([
            [
                'book_id' => 1,
                'index_id' => null,
                'title' => 'Capitulo 1',
                'page' => 15,
            ],
            [
                'book_id' => 1,
                'index_id' => 1,
                'title' => 'Capitulo 1.1',
                'page' => 16,
            ],
            [
                'book_id' => 1,
                'index_id' => 1,
                'title' => 'Capitulo 1.2',
                'page' => 20,
            ],
            [
                'book_id' => 2,
                'index_id' => null,
                'title' => 'Capitulo 1',
                'page' => 25,
            ],
            [
                'book_id' => 2,
                'index_id' => null,
                'title' => 'Capitulo 2',
                'page' => 35,
            ],
            [
                'book_id' => 2,
                'index_id' => 5,
                'title' => 'Capitulo 2.1',
                'page' => 38,
            ],
        ]);
    }
}
