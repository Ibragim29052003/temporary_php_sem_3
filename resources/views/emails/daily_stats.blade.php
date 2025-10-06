<!doctype html>
<html>
<body>
<p>Здравствуйте, {{ $moderator->name ?? 'Модератор' }},</p>

<p>Статистика за {{ $data['date'] ?? $data['timestamp'] }}:</p>
<ul>
    <li>Просмотров (всего): <strong>{{ $data['views_total'] }}</strong></li>
    <li>Уникальных статей просмотрено: <strong>{{ $data['unique_article_views'] }}</strong></li>
    <li>Новых комментариев: <strong>{{ $data['new_comments'] }}</strong></li>
</ul>

<p>С уважением,<br>Система уведомлений сайта</p>
</body>
</html>
