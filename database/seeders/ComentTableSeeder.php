<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class ComentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comment::truncate();

        // Agrega datos de ejemplo
        Comment::create([
            'text' => 'Todo es un lio en la politica',
            'sent_date' => 20240227,
            'published_date' => 20240228,
            'status' => 'PUBLISHED',
            'content_id' => 1,
            'user_id' => 1,
            'created_by' => 'seeder',
        ]);

        // Método factory para generar datos de ejemplo más complejos
        Comment::factory(10)->create();
    }
}
