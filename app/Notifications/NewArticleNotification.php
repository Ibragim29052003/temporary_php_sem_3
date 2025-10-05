<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Article;

class NewArticleNotification extends Notification
{
    use Queueable;

    protected $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    // Используем только database канал
    public function via($notifiable)
    {
        return ['database'];
    }

    // Метод toDatabase описывает данные, которые попадут в таблицу notifications
    public function toDatabase($notifiable)
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->title,
            'preview_url' => route('articles.show', $this->article->id),
            'author_name'  => $this->article->user->name,
            'published_at' => $this->article->published_at
                ? $this->article->published_at->format('d.m.Y H:i')
                : now()->format('d.m.Y H:i'),
        ];
    }
}
