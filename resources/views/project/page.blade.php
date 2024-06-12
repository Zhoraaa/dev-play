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
                        ? 'Подписавшись на обновления команды вы будете получать на почту уведомления об обновлениях проектов этого проекта'
                        : 'Отказаться от подписки на обновления проекта';
                @endphp
                <a href="{{ route('subscribe', ['type' => 'project', 'id' => $project->id]) }}"
                    class="btn btn-{{ $substyle }}" title="{{ $title }}">{{ $subtext }}</a>

                <a href="{{ route('buglist', ['project' => $project->url]) }}" class="btn btn-secondary">Найденные ошибки</a>

                {{-- Триггер модальки нового поста --}}
                <button class="btn btn-outline-secondary mb-1" data-bs-toggle="modal" data-bs-target="#bugreport">Я нашёл
                    ошибку!</button>
                {{-- Модалька нового поста --}}
                <form action="{{ route('postSave', ['from_team' => 0, 'team' => 0]) }}" method="post" class="modal fade"
                    id="bugreport" tabindex="-1" aria-labelledby="bugreportLabel" aria-hidden="true">
                    @csrf
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="bugreportLabel">Опишите ошибку.</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-floating mb-3">
                                    <textarea name="text" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! old('description') ?? null !!}</textarea>
                                    <label for="text">Что произошло?</label>
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
                                    <label for="filesMultiple" class="form-label">Можете приложить скриншоты</label>
                                    <input class="form-control" type="file" id="filesMultiple" name="images" multiple>
                                </div>
                                <select class="hidden" name="projID">
                                    <option class="form-check-label" for="show_true_author" value="{{ $project->id }}"
                                        selected>
                                        {{ $project->name }}
                                    </option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                                <button class="btn btn-success">Опубликовать</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
            @if ($canedit && auth()->user()->role_id >= 1)
                <a href="{{ route('snapshotNew', ['url' => $project->url]) }}" class="mr-1 mb-1 btn btn-success">+ Новая
                    версия</a>

                <a href="{{ route('buglist', ['project' => $project->url]) }}" class="btn btn-secondary">Найденные
                    ошибки</a>

                @if ($canedit === 2)
                    <a href="{{ route('projectEditor', ['url' => $project->url]) }}"
                        class="mr-1 mb-1 btn btn-warning">Редактировать
                        информацию</a>
                @endif

                @if ($canedit ===2 xor)
                    
                @endif
                <!-- Модалька подтверждения удаления -->
                <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                    <form class="modal-dialog" action="{{ route('projectDelete', ['url' => $project->url]) }}"
                        method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                    проект?</h1>
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
        <div class="mb-2">
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


        <div class="d-flex flex-wrap justify-content-between border-bottom mb-2">
            <p class="text-secondary d-block mb-1">
                Авторство:
            </p>
            @if ($project->author_mask)
                <a href="{{ route('devteam', ['url' => $project->author_mask_url]) }}" class="d-block">
                    {!! $project->author_mask !!}
                </a>
            @elseif ($project->author)
                <a href="{{ route('user', ['login' => $project->author]) }}" class="d-block">
                    {!! $project->author !!}
                </a>
            @else
                <i class="text-secondary">
                    Автор удалил аккаунт
                </i>
            @endif
        </div>
        @if ($taglist != '.')
            <div class="d-flex flex-wrap justify-content-between border-bottom mb-3">
                <p class="text-secondary d-block mb-1">
                    Теги:
                </p>
                <i class="text-secondary d-block">
                    {!! $taglist !!}
                </i>
            </div>
        @endif
        <div class="d-flex flex-wrap justify-content-between border-bottom mb-3">
            <p class="text-secondary d-block mb-1">
                Проект создан:
            </p>
            <span class="d-block">
                {!! $project->formatted_created_at !!}
            </span>
        </div>
        @if ($snapshots->all())
            <div class="d-flex flex-wrap justify-content-between border-bottom">
                <p class="text-secondary d-block mb-1">
                    Последнее обновление:
                </p>
                <div>
                    @php
                        $lastbuild = $snapshots->toArray()[0];
                    @endphp
                    <a href="{{ route('snapshot', ['url' => $project->url, 'build' => $lastbuild['name']]) }}">
                        {{ $lastbuild['name'] }}
                    </a>
                    <span>
                        - {!! $project->formatted_updated_at !!}
                    </span>
                </div>
            </div>
        @endif

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

    {{-- Подборка медиафайлов --}}
    <div class="w-75 m-auto mb-5">

        @if ($medias)
            {{-- Триггер модальки с медиа --}}
            <div class="col" data-bs-toggle="modal" data-bs-target="#mediaFiles">
                <h5>
                </h5>
                <div>
                    @foreach ($medias as $media)
                        <img src="{{ asset('storage/snapshots/media/' . $media['file_name']) }}" class="shadow-sm m-1"
                            alt="{{ $media['file_name'] }}" style="height: 100px; cursor: pointer">
                    @endforeach
                </div>
            </div>

            {{-- Модаль с медиа --}}
            <div class="modal modal-xl fade" id="mediaFiles" tabindex="-1" aria-labelledby="mediaFilesLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="mediaFilesLabel">Медиафайлы</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Закрыть"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Вывод медиа --}}
                            <div id="carouselExample" class="carousel slide">
                                <div class="carousel-inner rounded border overflow-hidden">
                                    {{-- Генерация слайдера с картинками --}}
                                    @foreach ($medias as $key => $media)
                                        <div class="carousel-item {{ $key === 0 ? 'active' : null }}">
                                            <div class="w-100 carousel-img-wrapper">
                                                <img src="{{ asset('storage/snapshots/media/' . $media['file_name']) }}"
                                                    class="d-block shadow" alt="{{ $media['file_name'] }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Предыдущий</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Следующий</span>
                                </button>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
