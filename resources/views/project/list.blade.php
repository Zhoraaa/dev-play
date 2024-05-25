@extends('layout')

@section('title')
    Главная - проекты
@endsection

@section('body')
    {{-- Триггер модальки фильтров --}}
    <div class="w-75 m-auto mt-2 d-flex flex-wrap justify-content-between">
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#filters">
            Настроить фильтры
        </button>
        @auth
            <div>
                @if (auth()->user()->role_id == 2 && !auth()->user()->banned)
                    <a href="{{ route('projectNew') }}" class="btn btn-success mb-2">+ Новый проект</a>
                    <a href="{{ route('home', ['author_id' => auth()->user()->id]) }}" class="btn btn-primary mb-2">Мои
                        проекты</a>
                @endif
            </div>
        @endauth
    </div>

    @if ($tagStr != '.')
        <div class="w-75 m-auto mb-3">
            <i>
                <b>
                    Выбранные теги:
                </b>
                {{ $tagStr }}
            </i>
            <br>
            <a href="{{ route('home') }}" class="mt-1 btn btn-secondary">Сбросить фильтры</a>
        </div>
    @endif

    {{-- Модалька фильтрации --}}
    <form action="{{ route('home') }}" method="project" class="modal fade" id="filters" tabindex="-1"
        aria-labelledby="filtersLabel" aria-hidden="true">
        @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="#" method="get">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="filtersLabel">Фильтры</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Поиск тегов --}}
                        <div class="form-group mb-3">
                            <input type="text" id="search" class="form-control" placeholder="Поиск по тегам...">
                        </div>
                        <div class="overflow-y-scroll mb-3" style="max-height: 30vh">
                            {{-- Генерация списка тегов --}}
                            @foreach ($tags as $tag)
                                <div class="form-check searchable">
                                    <input class="form-check-input" type="checkbox" id="tag{{ $tag->id }}"
                                        name="tag-{{ $tag->id }}"
                                        {{ isset($selectedTags) && in_array($tag->id, $selectedTags) ? 'checked' : null }}>
                                    <label class="form-check-label criteria" for="tag{{ $tag->id }}">
                                        {{ $tag->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <select class="form-select mb-3" name="sort">
                            <option value="1">Сначала новые</option>
                            <option value="2">Сначала старые</option>
                            <option value="3">Недавно обновлённые</option>
                            <option value="4">А-Я</option>
                            <option value="5">Я-А</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                        <button class="btn btn-success">Применить</button>
                    </div>
                </form>
            </div>
        </div>
    </form>

    {{-- Список проектов --}}
    @if (isset($projects))
        <div class="w-75 m-auto d-flex flex-wrap justify-content-evenly">
            @foreach ($projects as $project)
                <div class="card" style="width: 18rem; margin: 5px;">
                    @if ($project->cover)
                        <img src="{{ asset('storage/projects/covers/' . $project->cover) }}" class="card-img-top"
                            alt="{{ $project->cover }}">
                    @endif
                    <div class="card-body mt-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-baseline mb-1">
                            <a href="{{ route('project', ['url' => $project->url]) }}"
                                class="d-flex flex-wrap align-items-baseline text-decoration-none">
                                <h3>{{ $project->name }}</h3>
                            </a>
                            <div>
                                <div>
                                    Последнее обновление:
                                </div>
                                <div>
                                    {!! $project->formatted_updated_at !!}
                                </div>
                            </div>
                        </div>
                        @if ($project->author_team_url)
                            <a href="{{ route('devteam', ['url' => $project->author_team_url]) }}"
                                class="d-flex flex-wrap align-items-center mb-2 text-decoration-none text-secondary">
                                @if ($project->author_team_avatar)
                                    <div class="avatar avatar-small" style="margin-right: 10px">
                                        <img src="{{ asset('storage/imgs/teams/avatars/' . $project->author_team_avatar) }}"
                                            alt="">
                                    </div>
                                @endif
                                <p class="mb-0">
                                    <i>
                                        {{ $project->author_team }} (Команда разработчиков)
                                    </i>
                                </p>
                            </a>
                        @else
                            <a href="{{ route('user', ['login' => $project->author]) }}"
                                class="d-flex flex-wrap align-items-center mb-2 text-decoration-none text-secondary">
                                @if ($project->avatar)
                                    <div class="avatar avatar-small" style="margin-right: 10px">
                                        <img src="{{ asset('storage/imgs/users/avatars/' . $project->avatar) }}"
                                            alt="">
                                    </div>
                                @endif
                                <p class="mb-0">
                                    <i>
                                        {{ $project->author }} (Разработчик)
                                    </i>
                                </p>
                            </a>
                        @endif
                        <i class="text-secondary">
                            <b>
                                Теги:
                            </b>
                            {{ $project->tags }}
                        </i>
                        <div class="mb-2">
                            <p>
                                {!! mb_substr($project->description, 0, 600) !!}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
