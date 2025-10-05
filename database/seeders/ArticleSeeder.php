<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('articles.json'));
        $articles = json_decode($json, true);

        // // Проверяем, есть ли пользователь с ID = 1
        $user = User::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Администратор',
                'email' => 'admin@example.com',
                'password' => bcrypt('password')
            ]
        );

    
        foreach ($articles as $item) {
        // создаём случайную дату между 2024-01-01 и сегодня
            $randomDate = Carbon::createFromTimestamp(
                fake()->dateTimeBetween('2024-01-01', 'now')->getTimestamp()
            );
             // задаём случайное время
            $randomDate->setTime(rand(0,23), rand(0,59), rand(0,59));

            Article::create([
                'title'        => $item['name'],
                'body'         => $item['desc'],
                'user_id'       => $user->id,
                'published_at' => $randomDate,
                // 'preview_image'=> $item['preview_image'] ?? null,
                // 'full_image'   => $item['full_image'] ?? null,
                'preview_image'=> $item['preview_image'] ? basename($item['preview_image']) : 'placeholder_preview.png',
                'full_image'   => $item['full_image'] ? basename($item['full_image']) : 'placeholder_full.png',

            ]);
        }
    }
}
