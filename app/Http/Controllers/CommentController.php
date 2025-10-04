<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $article->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
            'is_approved' => false, // Новый комментарий не одобрен по умолчанию
        ]);

        return redirect()->route('articles.show', $article)
                         ->with('success', 'Ваш комментарий отправлен на модерацию!');
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

        return back()->with('success', 'Комментарий одобрен!');
    }

    /**
     * Отклонение комментария
     */
    public function reject(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Комментарий отклонён и удалён!');
    }

    // Обновление комментария
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment->update([
            'body' => $validated['body'],
            'is_approved' => false, // После редактирования комментарий снова требует модерации
        ]);

        return redirect()
                        ->route('articles.show', $comment->article_id)
                        ->with('success', 'Комментарий обновлён и отправлен на повторную модерацию!');
    }


    /**
     * Удаление комментария
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Комментарий удалён!');
    }
}
