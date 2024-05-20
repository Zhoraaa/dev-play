@extends('layout')

@section('title')
    {{ $team->name }}
@endsection
@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary {{ $team->banned ? 'alert alert-danger' : null }}">
        @if ($team->avatar)
            <div class="avatar avatar-big">
                <img src="{{ asset('storage/imgs/teams/avatars/' . $team->avatar) }}" alt="">
            </div>
        @endif
        <div class="d-flex flex-wrap justify-content-between mb-2">

            <h2 class="text-primary">
                {{ $team->name }}
                @if ($team->banned)
                    <i class="text-secondary">
                        (Пользователь забанен)
                    </i>
                @endif
            </h2>
            <div>
                @auth
                    @if (!$canedit)
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
                    @switch($canedit)
                        @case(1)
                        @case(2)
                            {{-- Триггер модальки нового поста --}}
                            <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#newPost">Новый
                                пост</button>
                            {{-- Модалька нового поста --}}
                            <form action="{{ route('postSave', ['from_team' => true, 'team' => $team->id]) }}" method="post"
                                class="modal fade" id="newPost" tabindex="-1" aria-labelledby="newPostLabel"
                                aria-hidden="true">
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
                                                <input class="form-control" type="file" id="filesMultiple" name="images"
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
                        @break

                        @case(1)
                        @case(2)
                            <a href="{{ route('projectNew') }}" class="btn btn-success mb-1">+ Новый проект</a>
                        @break

                        @case(2)
                            {{-- Триггер модальки настройки аватарки --}}
                            <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#avatarModal">Настройка
                                аватара</button>
                            {{-- Ссылка на редактор --}}
                            <a href="{{ route('devteamEditor', ['url' => $team->url]) }}"
                                class="btn btn-warning mb-1">Редактировать
                                информацию</a>
                            {{-- Триггер модальки удаления --}}
                            <button class="btn btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                                команду</button>
                        @break
                    @endswitch
            </div>
            {{-- Модалька обновления аватарки --}}
            <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('avatarUpdate') }}" method="POST" enctype="multipart/form-data">
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
                                <a href="{{ route('avatarDelete', ['login' => auth()->user()->login]) }}"
                                    class="btn btn-danger">Удалить аватар</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Модалька подтверждения удаления команды --}}
            <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                <form class="modal-dialog" action="{{ route('userdelete') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                аккаунт?</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Это действие нельзя будет отменить. Все упоминания о Вас на сайте исчезнут, некоторая
                            информация будет безвозвратно утрачена.
                            <div class="form-floating mt-3">
                                <input type="password" name="password" class="form-control" id="floatingPassword"
                                    placeholder="Password">
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
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between mb-3">
            <span class="text-secondary">
                Команда сформирована...
            </span>
            <span>
                {!! $team->created_at !!}
            </span>
        </div>
        <p>
            {!! $team->about !!}
        </p>
        <div>
            <b>
                Участники команды:
            </b>
            @foreach ($members as $member)
                <div class="d-flex flex-wrap align-items-center">
                    @if ($member->avatar)
                        <div class="avatar avatar-medium" style="margin-right: 10px">
                            <img src="{{ asset('storage/imgs/users/avatars/' . $member->avatar) }}" alt="">
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('userpage', ['login' => $member->login]) }}">
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
@endsection
