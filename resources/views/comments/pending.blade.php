@extends('layout')

@section('title', 'Модерация комментариев')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Комментарии на модерации</h1>

    @if($comments->isEmpty())
        <div class="alert alert-info">Нет комментариев, ожидающих проверки.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Автор</th>
                    <th>Комментарий</th>
                    <th>К статье</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>{{ $comment->user->name ?? 'N/A' }}</td>
                        <td>{{ $comment->body }}</td>
                        <td>
                            <a href="{{ route('articles.show', $comment->article_id) }}" target="_blank">
                                Статья #{{ $comment->article_id }}
                            </a>
                        </td>
                        <td class="d-flex gap-2">
                            <form action="{{ route('comments.approve', $comment) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm">Принять</button>
                            </form>

                            <form action="{{ route('comments.reject', $comment) }}" method="POST">
                                @csrf
                                <button class="btn btn-danger btn-sm">Отклонить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
