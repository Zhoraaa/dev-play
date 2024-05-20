@extends('layout')

@section('title')
    Статья от {{ $post->author }}
@endsection

@section('body')
    {{-- Основная статья --}}
    <div class="w-75 m-auto mb-1 p-2 rounded border border-dark">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <a href="{{ route('userpage', ['login' => $post->author]) }}" class="d-flex flex-wrap align-items-center">
                @if ($post->avatar)
                    <div class="avatar avatar-medium" style="margin-right: 10px">
                        <img src="{{ asset('storage/imgs/users/avatars/' . $post->avatar) }}" alt="">
                    </div>
                @endif
                <div>
                    <h5>
                        {{ $post->author }}
                    </h5>
                </div>
            </a>
            <div>
                {!! $post->created_at_formatted !!}
                @if ($canedit)
                    <a href="{{ route('postDel', ['id' => $post->id]) }}" class="btn btn-outline-danger">
                        Удалить пост
                    </a>
                @endif
            </div>
        </div>
        <div class="mb-3">
            <p>
                {!! $post->text !!}
            </p>
        </div>

        {{-- Триггер модальки с медиа --}}
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            Просмотреть изображения
        </button>

        {{-- Модаль с медиа --}}
        <div class="modal modal-xl fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Медиафайлы</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Вывод медиа --}}
                        @php
                            $media_files = explode(',', $post->media_files);
                        @endphp
                        <div id="carouselExample" class="carousel slide">
                            <div class="carousel-inner rounded border overflow-hidden">
                                {{-- Генерация слайдера с картинками --}}
                                @foreach ($media_files as $key => $media_file)
                                    <div class="carousel-item {{ $key === 0 ? 'active' : null }}">
                                        <div class="w-100 carousel-img-wrapper">
                                            <img src="{{ asset('storage/imgs/posts/media/' . $media_file) }}"
                                                class="d-block shadow" alt="...">
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
    </div>

    {{-- Блок с комментариями --}}

    <div class="w-75 m-auto mb-1 p-2">
        <div class="d-flex flex-wrap justify-content-between mb-2">
            <h5>Комменарии ({{ $commsCount }})</h5>
            <div>
                {{-- Триггер модальки комментария --}}
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#commForm">
                    Написать комментарий
                </button>
                {{-- Модалька нового комментария --}}
                <form action="{{ route('commNew', ['post_id' => $post->id]) }}" method="post" class="modal fade"
                    id="commForm" tabindex="-1" aria-labelledby="commFormLabel" aria-hidden="true">
                    @csrf
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="commFormLabel">Комментарий к посту</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-floating mb-3">
                                    <textarea name="text" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! $project->description ?? null !!}</textarea>
                                    <label for="text">Давным-давно...</label>
                                    <div class="editor-buttons mt-3">
                                        <button type="button" id="boldBtn"
                                            class="btn btn-outline-secondary"><b>Жирный</b></button>
                                        <button type="button" id="italicBtn"
                                            class="btn btn-outline-secondary"><i>Курсив</i></button>
                                        <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить
                                            ссылку</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                                <button class="btn btn-success">Опубликовать</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @if ($commsCount > 0)
            @foreach ($comms as $comm)
                <div class="w-100 mb-1 border"></div>
                <div class="p-2 mb-2">
                    <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center">
                        <a href="{{ route('userpage', ['login' => $comm->author]) }}"
                            class="d-flex flex-wrap align-items-center">
                            <div class="avatar avatar-small" style="margin-right: 10px">
                                <img src="{{ asset('storage/imgs/users/avatars/' . $comm->avatar) }}" alt="">
                            </div>
                            <div>
                                <h6 class="link-secondary">
                                    {{ $comm->author }}
                                </h6>
                            </div>
                        </a>
                        <div>
                            {!! $comm->created_at_formatted !!}
                            @auth
                                @if ($comm->author_id === auth()->user()->id)
                                    <a href="{{ route('commDel', ['id' => $comm->id]) }}" class="btn btn-outline-danger">
                                        Удалить комментарий
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="mb-3">
                        <p>
                            {!! $comm->text !!}
                        </p>
                    </div>
                </div>
            @endforeach
    </div>
    @endif
@endsection
