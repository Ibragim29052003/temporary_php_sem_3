<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestLog;
use App\Models\Comment;
use App\Models\User;
use App\Mail\DailyStatsMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailyStats extends Command
{
    protected $signature = 'stats:send';
    protected $description = 'Send site statistics to moderators';

    public function handle()
    {
        // ---------------------------
        // ВАРИАНТ 1: статистика за минуту
        // ---------------------------
        $now = Carbon::now();
        $oneMinuteAgo = $now->copy()->subMinute();

        // Просмотры за последнюю минуту
        $viewsMinute = RequestLog::where('created_at', '>=', $oneMinuteAgo)->count();

        // Уникальные статьи за последнюю минуту
        $uniqueArticleViews = RequestLog::where('created_at', '>=', $oneMinuteAgo)
            ->distinct('article_id')
            ->count('article_id');

        // Новые комментарии за последнюю минуту
        $commentsCount = Comment::where('created_at', '>=', $oneMinuteAgo)->count();

        $data = [
            'period' => 'last_minute',
            'timestamp' => $now->toDateTimeString(),
            'date' => $now->toDateString(),
            'views_total' => $viewsMinute,
            'unique_article_views' => $uniqueArticleViews,
            'new_comments' => $commentsCount,
        ];

        // Отправка модераторам
        $moderatorEmail = env('MODERATOR_EMAIL'); // можно заменить на $mod->email для каждого модератора
        Mail::to($moderatorEmail)->send(new DailyStatsMail($data));

        $this->info('Stats for the last minute sent to moderator.');

        // Очистка просмотренных записей за эту минуту
        RequestLog::where('created_at', '<=', $now)->delete();

        // ---------------------------
        // ВАРИАНТ 2: статистика за день
        // ---------------------------
        /*
        $today = Carbon::today();

        $viewsToday = RequestLog::whereDate('created_at', $today)->count();

        $uniqueArticleViewsToday = RequestLog::whereDate('created_at', $today)
            ->whereNotNull('article_id')
            ->distinct('article_id')
            ->count('article_id');

        $commentsCountToday = Comment::whereDate('created_at', $today)->count();

        $dailyData = [
            'period' => 'today',
            'date' => $today->toDateString(),
            'views_total' => $viewsToday,
            'unique_article_views' => $uniqueArticleViewsToday,
            'new_comments' => $commentsCountToday,
        ];

        Mail::to($moderatorEmail)->send(new DailyStatsMail($dailyData));

        $this->info('Daily stats sent to moderator.');

        // Очистка логов дневных просмотров
        RequestLog::whereDate('created_at', $today)->delete();
        */

        return 0;
    }
}