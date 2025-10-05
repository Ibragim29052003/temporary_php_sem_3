<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём роли
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $reader    = Role::firstOrCreate(['name' => 'reader']);

        // Создаём администратора/модератора
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => bcrypt('password'),
                'role_id' => $moderator->id,
            ]
        );

        // Сидаем статьи
        $this->call([
            ArticleSeeder::class,
        ]);

        $articles = Article::all();

        if ($articles->count() > 0) {
            // Создаём 30 комментариев, распределяем по статьям
            Comment::factory(30)->make()->each(function ($comment) use ($articles, $admin) {
                $comment->article_id = $articles->random()->id;
                $comment->user_id = $admin->id;      // можно выбрать случайного пользователя, если есть
                $comment->is_approved = true;        // сразу одобренные, чтобы отображались
                $comment->save();
            });
        }
    }
}
