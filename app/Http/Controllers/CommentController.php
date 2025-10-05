<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    /**
     * Сохранение нового комментария
     */
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $isApproved = auth()->user()->isModerator() ? true : false; // админ сразу одобрен

        $article->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
            'is_approved' => $isApproved, 
        ]);

        Cache::forget('article_' . $article->id);
        Cache::forget('articles_home');

        $message = $isApproved
            ? 'Комментарий опубликован!'
            : 'Ваш комментарий отправлен на модерацию!';

        return redirect()->route('articles.show', $article)
                         ->with('success', $message);
    }

    // Форма редактирования
    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);
        return view('comments.edit', compact('comment'));
    }


    /**
    * Список непроверенных комментариев
    */
    public function pending()
    {
        $comments = Comment::where('is_approved', false)->latest()->get();

        return view('comments.pending', compact('comments'));
    }

    /**
     * Подтверждение комментария
     */
    public function approve(Comment $comment)
    {
        $comment->update(['is_approved' => true]);

        Cache::forget('article_' . $comment->article_id);
        Cache::forget('articles_home');

        return back()->with('success', 'Комментарий одобрен!');
    }

    /**
     * Отклонение комментария
     */
    public function reject(Comment $comment)
    {
        $articleId = $comment->article_id;
        $comment->delete();

        Cache::forget('article_' . $articleId);
        Cache::forget('articles_home');

        return back()->with('success', 'Комментарий отклонён и удалён!');
    }

   // Обновление комментария
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        // Админский комментарий публикуется сразу
        $isApproved = auth()->user()->isModerator() ? true : false;

        // Для обычного пользователя — на модерацию
        if (!auth()->user()->isModerator() && auth()->id() === $comment->user_id) {
            $isApproved = false;
        }

        $comment->update([
            'body' => $validated['body'],
            'is_approved' => $isApproved,
        ]);

        Cache::forget('article_' . $comment->article_id);
        Cache::forget('articles_home');

        $message = $isApproved
            ? 'Комментарий обновлён и сразу опубликован!'
            : 'Комментарий обновлён и отправлен на повторную модерацию!';

        return redirect()
            ->route('articles.show', $comment->article_id)
            ->with('success', $message);
    }



    /**
     * Удаление комментария
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $articleId = $comment->article_id;

        $comment->delete();

        Cache::forget('article_' . $comment->article_id);
        Cache::forget('articles_home');

        return back()->with('success', 'Комментарий удалён!');
    }
}
