@extends('layout')

@section('title')
    {{ $buglist ? 'Отчёты об ошибках "' . $project->name . '"' : 'Лента новостей' }}
@endsection

@section('body')
    @if ($buglist)
        <a href="{{ route('project', ['url' => $project->url]) }}" class="btn btn-secondary d-block w-75 m-auto mb-2">
            ← Вернуться на страницу проекта
        </a>
    @endif

    @auth
        @if (!$buglist)
            {{-- Триггер модальки нового поста --}}
            <div class="w-75 m-auto mt-2 mb-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postEditorModal">
                    Новый пост
                </button>
            </div>

            {{-- Модалька нового поста --}}
            <form action="{{ route('postSave', ['from_team' => 0, 'team' => 0]) }}" enctype="multipart/form-data" method="post"
                class="modal fade" id="postEditorModal" tabindex="-1" aria-labelledby="postEditorModalLabel"
                aria-hidden="true">
                @csrf
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="postEditorModalLabel">Написать пост</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input class="form-control" type="file" id="filesMultiple" name="images[]" multiple>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                            <button class="btn btn-success">Опубликовать</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endauth

    {{-- Список постов --}}
    @if (isset($news))
        @foreach ($news as $post)
            <div class="w-75 m-auto mb-1 p-2 rounded border border-dark">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                    {{-- Автор - команда --}}
                    @if ($post->author_mask)
                        <div class="d-flex flex-wrap justify-content-start">
                            <a href="{{ route('devteam', ['url' => $post->showing_author_url]) }}"
                                class="d-flex flex-wrap align-items-center text-decoration-none">
                                {{-- Проверка наличия аватарки --}}
                                @if ($post->showing_author_avatar)
                                    <div class="avatar rounded-circle avatar-medium" style="margin-right: 10px">
                                        <img src="{{ asset('storage/imgs/teams/avatars/' . $post->showing_author_avatar) }}"
                                            alt="">
                                    </div>
                                @endif
                                <h5 class="d-block" style="margin-right:5px">
                                    {{ $post->showing_author }}
                                </h5>
                            </a>
                            {{-- Надо показать настоящего автора --}}
                            @if ($post->show_true_author)
                                <a href="{{ route('user', ['login' => $post->author]) }}" class="text-decoration-none">
                                    <h5>
                                    <i class="text-secondary fw-light">
                                        ({{ $post->author }})
                                    </i>
                                    </h5>
                                </a>
                            @endif
                        </div>
                    @else
                        {{-- Автор - пользователь --}}
                        <a href="{{ route('user', ['login' => $post->author]) }}"
                            class="d-flex flex-wrap align-items-center text-decoration-none">
                            @if ($post->avatar)
                                <div class="avatar rounded-circle avatar-medium" style="margin-right: 10px">
                                    <img src="{{ asset('storage/imgs/users/avatars/' . $post->avatar) }}" alt="">
                                </div>
                            @endif
                            <div>
                                @php
                                    switch ($post->role_id) {
                                        default:
                                            $nickStyle = 'primary';
                                            break;

                                        case 2:
                                            $nickStyle = 'success';
                                            break;

                                        case 3:
                                            $nickStyle = 'warning';
                                            break;

                                        case 4:
                                            $nickStyle = 'danger';
                                            break;
                                    }
                                @endphp
                                <h5 class="link-{{ $nickStyle }}">
                                    {{ $post->author }}
                                </h5>
                            </div>
                        </a>
                    @endif
                    <div>
                        {!! $post->formatted_created_at !!}
                    </div>
                </div>
                <div class="mb-3">
                    <p>
                        {!! mb_substr(strip_tags($post->text), 0, 600) !!}
                    </p>
                </div>
                <div class="">
                    <a href="{{ route('post', ['id' => $post->id]) }}" class="btn btn-primary">
                        {{ $buglist ? 'Смотреть подробнее' : 'Перейти к обсуждению' }} →
                    </a>
                    @auth
                        @if ($post->author_id === auth()->user()->id || auth()->user()->role_id >= 3)
                            <a href="{{ route('postDel', ['id' => $post->id]) }}" class="btn btn-outline-danger">
                                Удалить {{ $buglist ? 'отчёт' : 'пост' }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        @endforeach
    @endif
@endsection
