<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Новости')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
        }
        .card img {
            object-fit: cover;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app"></div>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">Новости</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    {{-- Модераторы --}}
                    @if(auth()->user()?->isModerator())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('articles.index') }}">Все статьи</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('articles.create') }}">Создать новость</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('comments.pending') }}">Модерация</a>
                        </li>
                    @endif

                    {{-- Обычные пользователи / читатели --}}
                    @if(auth()->check() && !auth()->user()?->isModerator())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarNotifications" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                                Уведомления
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge bg-danger">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarNotifications">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <li>
                                        <a class="dropdown-item" href="{{ $notification->data['preview_url'] }}">
                                            {{ $notification->data['title'] }} <br>
                                            <small>Опубликовано: {{ $notification->data['published_at'] }}</small><br>
                                            <small>Автор: {{ $notification->data['author_name'] }}</small>
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item">Нет новых уведомлений</span></li>
                                @endforelse
                            </ul>
                        </li>
                    @endif

                @endauth

                {{-- Остальные ссылки --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('about') }}">О нас</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}">Контакты</a>
                </li>
            </ul>

            {{-- Права доступа справа: Гость / Авторизованный --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login.form') }}">Войти</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register.form') }}">Регистрация</a></li>
                @else
                    <li class="nav-item"><span class="nav-link">Привет, {{ auth()->user()->name }}</span></li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">Выйти</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>



<div class="container">
    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
