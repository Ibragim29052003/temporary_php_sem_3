<?php

namespace App\Events;

// Событие автоматически транслируется на вебсокетный канал test.

// Можно использовать для обновления UI в реальном времени при добавлении статьи.

use App\Models\Article;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewArticleEvent implements ShouldBroadcast
{
    // Подключаем трейты:
    // Dispatchable → позволяет событие диспатчить через event()
    // InteractsWithSockets → для работы с websocket-сокетами
    // SerializesModels → сериализует модели для очередей
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $article; // Данные статьи, которые будут переданы в событии

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

     public function broadcastOn()
    {
        return new Channel('articles'); // имя канала
    }

    // какие данные придут на клиент
    public function broadcastWith()
    {
        return [
            'article' => [
                'id' => $this->article->id,
                'title' => $this->article->title,
                'preview_url' => route('articles.show', $this->article->id),
            ],
        ];
    }
}
