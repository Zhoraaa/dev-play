@extends('layout')

@section('title')
    {{ $team->name }}
@endsection
@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary {{ $team->banned ? 'alert alert-danger' : null }}">
        @if ($team->avatar)
            <div class="avatar rounded-circle avatar-big">
                <img src="{{ asset('storage/imgs/teams/avatars/' . $team->avatar) }}" alt="">
            </div>
        @endif
        <h2 class="text-primary">
            {{ $team->name }}
        </h2>
        <div class="mb-3">
            @auth
                @if ($invited)
                    {{-- Триггер модальки ответа --}}
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#inviteResponse">
                        Вы приглашены
                    </button>

                    {{-- Модалька ответа --}}
                    <div class="modal fade" id="inviteResponse" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="inviteResponseLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="inviteResponseLabel">Вы приглашены</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    Глава этой команды пригласил вас стать её частью, что скажете?
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('response', ['team' => $team->id, 'user' => Auth::user()->id, 'response' => 0]) }}"
                                        class="btn btn-outline-danger">Отказать</a>
                                    <a href="{{ route('response', ['team' => $team->id, 'user' => Auth::user()->id, 'response' => 1]) }}"
                                        class="btn btn-outline-success">Принять</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif @if (!$canedit)
                        {{-- Подписка --}}
                        @php
                            $substyle = !$subscribed ? 'success' : 'secondary';
                            $subtext = !$subscribed ? 'Подписаться' : 'Отписаться';
                            $title = !$subscribed
                                ? 'Подписавшись на обновления команды вы будете получать на почту уведомления об обновлениях проектов этой команды'
                                : 'Отказаться от подписки на обновления команды';
                        @endphp
                        <a href="{{ route('subscribe', ['type' => 'dev_team', 'id' => $team->id]) }}"
                            class="btn btn-{{ $substyle }}" title="{{ $title }}">{{ $subtext }}</a>
                    @endif
                @endauth
                {{-- Управление --}}
                @if ($canedit)
                    {{-- Триггер модальки нового поста --}}
                    <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#newPost">Новый
                        пост</button>
                    {{-- Модалька нового поста --}}
                    <form action="{{ route('postSave', ['from_team' => true, 'team' => $team->id]) }}" method="post"
                        class="modal fade" id="newPost" tabindex="-1" aria-labelledby="newPostLabel" aria-hidden="true">
                        @csrf
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="newPostLabel">Написать пост</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <textarea name="text" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! old('description') ?? null !!}</textarea>
                                        <label for="text">Что нового?</label>
                                        <div class="editor-buttons mt-3">
                                            <button type="button" id="boldBtn"
                                                class="btn btn-outline-secondary"><b>Жирный</b></button>
                                            <button type="button" id="italicBtn"
                                                class="btn btn-outline-secondary"><i>Курсив</i></button>
                                            <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить
                                                ссылку</button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="filesMultiple" class="form-label">Изображения</label>
                                        <input class="form-control" type="file" id="filesMultiple" name="images[]"
                                            multiple>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_true_author"
                                            name="show_true_author">
                                        <label class="form-check-label" for="show_true_author">
                                            Показывать имя автора
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                                    <button class="btn btn-success">Опубликовать</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('projectNew') }}" class="btn btn-success mb-1">+ Новый проект</a>
                @endif
                @if ($canedit === 1)
                    {{-- Триггер модальки подтверждения --}}
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#exitTeam">
                        Покинуть команду
                    </button>

                    {{-- Модалька подтверждения --}}
                    <div class="modal fade" id="exitTeam" tabindex="-1" aria-labelledby="exitTeamLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exitTeamLabel">Вы уверены?</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    Вы точно хотите выйти из команды {{ $team->name }}?
                                    <br>
                                    Это действие нельзя быдет отменить.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        data-bs-dismiss="modal">Отмена</button>
                                    <a href="{{ route('exit', ['team' => $team->id]) }}" class="btn btn-secondary">Да, я
                                        уверен</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($canedit === 2)
                    {{-- Триггер модальки настройки аватарки --}}
                    <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#avatarModal">Настройка
                        аватара</button>
                    {{-- Модалька обновления аватарки --}}
                    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('teamAvatarUpdate', ['url' => $team->url]) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="avatarModalLabel">Обновить аватар</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <input class="form-control" type="file" id="formFile" name="avatar">
                                            <small class="text-secondary"><i>Старые аватарки не
                                                    сохраняются;</i></small><br>
                                            <small class="text-secondary"><i>Поддерживаемые форматы: .jpg,
                                                    .jpeg, .png, .gif;</i></small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Обновить аватар</button>
                                        <a href="{{ route('teamAvatarDelete', ['url' => $team->url]) }}"
                                            class="btn btn-danger">Удалить аватар</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    {{-- Ссылка на редактор --}}
                    <a href="{{ route('devteamEditor', ['url' => $team->url]) }}"
                        class="btn btn-warning mb-1">Редактировать
                        информацию</a>
                @endif
                @auth
                    @if ($canedit === 2 || auth()->user()->role_id >= 3)
                        {{-- Триггер модальки удаления --}}
                        <button class="btn btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                            команду</button>
                        {{-- Модалька подтверждения удаления команды --}}
                        <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel"
                            aria-hidden="true">
                            <form class="modal-dialog" action="{{ route('devteamDelete', ['url' => $team->url]) }}"
                                method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите
                                            расформировать команду?</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Это действие нельзя будет отменить. Все упоминания о команде на сайте исчезнут,
                                        некоторая
                                        информация будет безвозвратно утрачена.
                                        <div class="form-floating mt-3">
                                            <input type="password" name="password" class="form-control"
                                                id="floatingPassword" placeholder="Password">
                                            <label for="floatingPassword">Для подтверждения введите пароль.</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Нет</button>
                                        <button type="submit" class="btn btn-danger">Да</button>
                                    </div>
                                </div>
                            </form>
                    @endif
                @endauth
        </div>
        <div class="d-flex flex-wrap justify-content-between mb-3">
            <span class="text-secondary">
                Команда сформирована...
            </span>
            <span>
                {!! $team->formatted_created_at !!}
            </span>
        </div>
        <p>
            {!! $team->about !!}
        </p>
        <div class="row">
            @if (!empty($members->all()))
                <div class="col">
                    <b>
                        Участники команды:
                    </b>
                    <div class="overflow-y-scroll" style="max-height:50vh">
                        @foreach ($members as $member)
                            <div class="d-flex flex-wrap align-items-center border p-2 pt-1 mb-2 rounded shadow-sm">
                                @if ($member->avatar)
                                    <div class="avatar rounded-circle avatar-medium" style="margin-right: 10px">
                                        <img src="{{ asset('storage/imgs/users/avatars/' . $member->avatar) }}"
                                            alt="">
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('user', ['login' => $member->login]) }}">
                                        <b>
                                            {{ $member->login }}
                                        </b>
                                    </a>
                                    <br>
                                    <i class="text-secondary">
                                        {{ $member->role }}
                                    </i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (!empty($projects->all()))
                <div class="col">
                    <b>
                        Проекты:
                    </b>
                    <div class="overflow-y-scroll" style="max-height: 50vh">
                        @foreach ($projects as $project)
                            <div class="mb-3 p-2 rounded border shadow-sm">
                                <div class="d-flex flex-wrap justify-content-between align-items-baseline mb-1">
                                    <a href="{{ route('project', ['url' => $project->url]) }}"
                                        class="d-flex flex-wrap align-items-baseline text-decoration-none">
                                        <h3>{{ $project->name }}</h3>
                                    </a>
                                </div>
                                <a href="{{ route('user', ['login' => $project->author]) }}"
                                    class="d-flex flex-wrap align-items-center mb-2 text-decoration-none text-secondary">
                                    <div class="avatar rounded-circle avatar-small" style="margin-right: 10px">
                                        <img src="{{ asset('storage/imgs/users/avatars/' . $project->avatar) }}"
                                            alt="">
                                    </div>
                                    <p class="mb-0">
                                        <i>
                                            {{ $project->author }}
                                        </i>
                                    </p>
                                </a>
                                <i class="text-secondary">
                                    <b>
                                        Теги:
                                    </b>
                                    {{ $project->tags }}
                                </i>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (!empty($posts->all()))
                <div class="col">
                    <b>
                        Последние новости:
                    </b>
                    <div class="overflow-y-scroll" style="max-width: 50vh">
                        @foreach ($posts as $post)
                            <a href="{{ route('post', ['id' => $post->id]) }}"
                                class="d-block text-decoration-none text-dark shadow-sm">
                                <div class="p-2 mb-3 rounded border">
                                    <div class="mb-2">
                                        {!! mb_strlen($post->text) <= 200 ? $post->text : mb_substr($post->text, 0, 200) . '...' !!}
                                    </div>
                                    <small>
                                        {!! $post->formatted_created_at !!}
                                    </small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
