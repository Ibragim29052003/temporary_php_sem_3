<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    public function index()
    {
        // Ключ кэша
        $cacheKey = 'articles_home';

        // Получаем статьи из кэша или создаём новый кэш
        $articles = Cache::remember($cacheKey, 60*60, function () {
            return Article::with('user')
                ->withCount(['comments as comments_count' => function ($q) {
                    $q->where('is_approved', true);
                }])
                ->get(); // все статьи без пагинации
        });

        return view('welcome', compact('articles'));
    }

    public function show($img)
    {
        return view('main.full_image', compact('img'));
    }
}
