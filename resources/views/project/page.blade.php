@extends('layout')

@php
    $string = $canedit ? $project->name . ' - Панель разработчика' : $project->name;
    $taglist = '';
    foreach ($tags as $tag) {
        $taglist .=
            '<a href=\'/?tag-' . $tag->id . '=on\' class="link-primary link-primary-hover">' . $tag->name . '</a>, ';
    }
    $taglist = mb_substr($taglist, 0, -2) . '.';
@endphp

@section('title')
    {{ $string }}
@endsection

@section('body')
    <div class="m-auto p-3 w-75 rounded border border-secondary">
        <div class="mb-2">
            @if (!$canedit)
                @php
                    $substyle = !$subscribed ? 'success' : 'secondary';
                    $subtext = !$subscribed ? 'Подписаться' : 'Отписаться';
                    $title = !$subscribed
                        ? 'Подписавшись на обновления команды вы будете получать на почту уведомления об обновлениях проектов этого разработчика'
                        : 'Отказаться от подписки на обновления разработчика';
                @endphp
                <a href="{{ route('subscribe', ['type' => 'project', 'id' => $project->id]) }}"
                    class="btn btn-{{ $substyle }}" title="{{ $title }}">{{ $subtext }}</a>
            @endif
            @if ($canedit && auth()->user()->role_id >= 1)
                <a href="{{ route('snapshotNew', ['url' => $project->url]) }}" class="mr-1 mb-1 btn btn-success">+ Новая
                    версия</a>
                {{-- Триггер модальки настройки oбложки --}}
                <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#avatarModal">Настройка
                    обложки</button>
                {{-- Модалька обновления oбложки --}}
                <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('coverUpdate', ['url' => $project->url]) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="avatarModalLabel">Обновить обложку</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <input class="form-control" type="file" id="formFile" name="cover">
                                        <small class="text-secondary"><i>Старые аватарки не
                                                сохраняются;</i></small><br>
                                        <small class="text-secondary"><i>Поддерживаемые форматы: .jpg,
                                                .jpeg, .png, .gif;</i></small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Обновить аватар</button>
                                    <a href="{{ route('coverUpdate', ['url' => $project->url]) }}"
                                        class="btn btn-danger">Удалить обложку</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @if ($canedit === 2)
                    <a href="{{ route('projectEditor', ['url' => $project->url]) }}"
                        class="mr-1 mb-1 btn btn-warning">Редактировать
                        информацию</a>
                    <button class="mr-1 mb-1 btn btn-danger" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                        проект</button>
                @endif

                <!-- Модалька подтверждения удаления -->
                <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                    <form class="modal-dialog" action="{{ route('projectDelete', ['url' => $project->url]) }}"
                        method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                    аккаунт?</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Это действие нельзя будет отменить. Все упоминания о проекте на сайте исчезнут,
                                некоторая
                                информация будет безвозвратно утрачена.
                                <div class="form-floating mt-3">
                                    <input type="password" name="password" class="form-control" id="floatingPassword"
                                        placeholder="Password">
                                    <label for="floatingPassword">Для подтверждения введите пароль.</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Нет</button>
                                <button type="submit" class="btn btn-danger">Да</ф>
                            </div>
                        </div>
                    </form>
            @endif
        </div>
        {{-- Информация о проекте --}}
        <div class="mb-2 d-flex flex-wrap justify-content-between">
            <h2>
                {{ $project->name }}
            </h2>
            @if ($project->cover)
                <div class="cover-wrapper rounded">
                    <img src="{{ asset('storage/projects/covers/' . $project->cover) }}" alt="">
                </div>
            @endif
        </div>

        <p class="mt-3 mb-3">
            {!! $project->description !!}
        </p>


        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Авторство:
            </p>
            @if ($project->author_mask)
                <a href="{{ route('devteam', ['url' => $project->author_mask_url]) }}" class="d-block">
                    {!! $project->author_mask !!}
                </a>
            @else
                <a href="{{ route('user', ['login' => $project->author]) }}" class="d-block">
                    {!! $project->author !!}
                </a>
            @endif
        </div>
        @if ($taglist != '.')
            <div class="d-flex flex-wrap justify-content-between">
                <p class="text-secondary d-block">
                    Теги:
                </p>
                <i class="text-secondary d-block">
                    {!! $taglist !!}
                </i>
            </div>
        @endif
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Проект создан:
            </p>
            <span class="d-block">
                {!! $project->formatted_created_at !!}
            </span>
        </div>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Последнее обновление:
            </p>
            <span class="d-block">
                {!! $project->formatted_updated_at !!}
            </span>
        </div>
    </div>
    </div>

    <div class="m-auto mt-3 mb-3 p-3 w-75 rounded border border-secondary">
        <h5>
            Версии проекта:
        </h5>
        @if ($snapshots->all())
            @foreach ($snapshots as $snapshot)
                <a
                    href="{{ route('snapshot', ['url' => $url, 'build' => $snapshot->name]) }}">{{ $snapshot->name }}</a><br>
            @endforeach
        @else
            <i>Нет опубликованых версий</i>
        @endif
    </div>
@endsection
