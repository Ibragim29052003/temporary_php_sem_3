<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use App\Models\Article;

class LogRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Выполнить контроллер и получить ответ — логируем **после** проверки успешного выполнения
        $response = $next($request);

        // Логируем только GET-запросы к страницам статей
        if ($request->isMethod('GET')) {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            // Примем, что имя маршрута для просмотра статьи — 'articles.show'
            if ($routeName === 'articles.show' || $request->is('articles/*')) {
                $articleParam = $request->route('article'); // может быть Article объект или id
                $articleId = $articleParam instanceof Article ? $articleParam->id : null;

                RequestLog::create([
                    'user_id'    => auth()->id(),
                    'article_id' => $articleId,
                    'url'        => $request->fullUrl(),
                    'method'     => $request->method(),
                    'ip'         => $request->ip(),
                ]);
            }
        }

        return $response;
    }
}
