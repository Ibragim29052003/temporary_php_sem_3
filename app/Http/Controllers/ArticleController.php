<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Mail\ArticleCreatedMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Jobs\VeryLongJob;
use App\Events\NewArticleEvent;
use App\Notifications\NewArticleNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;


class ArticleController extends Controller
{
    /**
     * Список всех статей
     */
    // public function index()
    // {
    //     // Eager load автора статьи
    //     $articles = Article::with('user')->get();
    //     $articles = Article::with('user')->withCount('comments')->get();
    //     return view('articles.index', compact('articles'));
    // }

    public function __construct()
    {
        // Только зарегистрированные пользователи могут создавать, редактировать и удалять статьи
        $this->middleware('auth')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }


    public function index(Request $request)
    {
        // Проверка прав доступа: только модератор может видеть все статьи
        if (auth()->check() && auth()->user()->isModerator()) {
            $articles = Article::with('user')
                ->withCount(['comments as comments_count' => function ($q) {
                    $q->where('is_approved', true);
                }])
                ->get(); // показываем все статьи модератору
        } else {
            // Обычные пользователи не могут заходить напрямую на /articles
            if (request()->routeIs('articles.index')) {
                abort(403); // Forbidden
            }

            // Главная страница с пагинацией и кэшом
            $page = $request->get('page', 1);
            $cacheKey = 'articles_page_' . $page;

            $articles = Cache::remember($cacheKey, 60*60, function () {
                return Article::with('user')
                    ->withCount(['comments as comments_count' => function ($q) {
                        $q->where('is_approved', true);
                    }])
                    ->paginate(6); // лимит на страницу
            });
        }

        return view('articles.index', compact('articles'));
    }




    /**
     * Форма создания статьи
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Сохраняем новую статью
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'published_at' => 'required|date',
        ]);

        $publishedAt = \Carbon\Carbon::parse($request->published_at)
            ->setSecond(rand(0, 59));

        $data = [
            'title'        => $request->title,
            'body'         => $request->body,
            'user_id'      => Auth::id(),
            'published_at' => $publishedAt,
            // всегда ставим дефолтную картинку
            'preview_image'=> 'placeholder_preview.png',
            'full_image'   => 'placeholder_full.png',
        ];

        $article = Article::create($data);

        // Берём всех читателей (role_id = 2), кроме автора статьи
        $readers = User::where('role_id', 2)
            ->where('id', '!=', Auth::id())
            ->get();

        if ($readers->isNotEmpty()) {
            Notification::send($readers, new NewArticleNotification($article));
        }

        // Очищаем кэш главной страницы и пагинации
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget('articles_page_' . $i);
        }

        // // Отправляем письмо модератору 8 ЛАБА
        // Mail::to(config('mail.moderator'))->send(new ArticleCreatedMail($article));
        
        // диспатч события (будет вещаться)
        event(new NewArticleEvent($article));

        // Отправляем письмо через очередь
        VeryLongJob::dispatch($article);

        return redirect()->route('articles.index')->with('success', 'Статья успешно создана!');
    }

       
    /**
     * Просмотр отдельной статьи
     */
    public function show(Article $article)
    {
        $cacheKey = 'article_' . $article->id;

       // кэширование отдельной статьи с комментариями через теги
        $article = Cache::rememberForever($cacheKey, function () use ($article) {
            return $article->load(['user', 'approvedComments.user']);
        });

        if (auth()->check()) {
            $notifications = auth()->user()
                ->unreadNotifications()
                ->where('data->article_id', $article->id) // обращаемся к JSON-полю
                ->get();

            foreach ($notifications as $notification) {
                $notification->markAsRead();
            }
        }
        return view('articles.show', compact('article'));
    }

    /**
     * Форма редактирования статьи
     */
    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    /**
     * Обновляем статью
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'published_at' => 'required|date',
        ]);
        // Генерация секунд при редактировании
        $publishedAt = \Carbon\Carbon::parse($request->published_at)
            ->setSecond(rand(0, 59));

         // Обновляем статью
        $article->update([
            'title' => $request->title,
            'body' => $request->body,
            'published_at' => $publishedAt,
        ]);

        // Удаляем кэш страницы просмотра статьи
        Cache::forget('article_' . $article->id);

        // Удаляем кэш главной страницы и пагинации
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget('articles_page_' . $i);
        }

        return redirect()->route('articles.index')->with('success', 'Статья успешно обновлена!');
    }

    /**
     * Удаляем статью
     */
    public function destroy(Article $article)
    {
        $article->delete();

        // Удаляем кэш страницы статьи
        Cache::forget('article_' . $article->id);

        // Удаляем кэш главной страницы и пагинации
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget('articles_page_' . $i);
        }

        return redirect()->route('articles.index')->with('success', 'Статья успешно удалена!');
    }
    

}
