<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        $contents = Content::select('id', 'title', 'alias')->where('status', 'PUBLISHED')->get();
        $content = $contents->random(1)->first();

        $title = $this->faker->word;
        return [
            'text' => $title,
            'sent_date' => rand(20240101, intval(date('Ymd'))),
            'published_date' => rand(20240101, intval(date('Ymd'))),
            'status' => $this->faker->randomElement(['PUBLISHED', 'NOT_PUBLISHED']),
            'content_id' => $content->id,
            'user_id' =>1,
            'created_by' => 'factory'
        ];
    }
}
