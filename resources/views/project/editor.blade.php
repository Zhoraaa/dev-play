@extends('layout')


@section('title')
    Редактирование проекта
@endsection

@section('body')
    <div class="m-auto w-75 mt-3">
        <a href="{{ URL::previous() }}" class="d-block mt-3 btn btn-secondary">← Назад</a>
    </div>
    {{-- Редактирование --}}
    <form action="{{ route('projectSaveChanges') }}" method="POST" enctype="multipart/form-data" class="m-auto mt-3 w-75">
        @csrf
        @if (isset($projectdata))
            <input type="hidden" value="{{ $projectdata->id ?? null }}" name="id">
        @endif
        <div class="row mb-3">
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="name"
                        value="{{ $projectdata->name ?? null }}">
                    <label for="name">Название</label>
                </div>
            </div>
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="url" class="form-control" id="url"
                        value="{{ $projectdata->url ?? null }}">
                    <label for="url">URL</label>
                    <small class="text-secondary"><i>Ссылка на проект, например exampleProj</i></small>
                </div>
            </div>
            <div class="col mt-3">
                <!-- Вызов модали -->
                <button type="button" class="w-100 pt-3 pb-3 btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#tagsModal">
                    Список тегов
                </button>
            </div>
        </div>
        <div class="form-floating mb-3">
            <textarea name="description" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! $projectdata->description ?? null !!}</textarea>
            <label for="description">Описание вашего проекта</label>
            <div class="editor-buttons mt-3">
                <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="formFile" class="form-label">Обложка</label>
                <input class="form-control" type="file" id="formFile">
            </div>
            <div class="col">
                <label for="avatar">Текущая обложка:</label>
                @if (isset($projectdata->cover))
                    <img id="avatar" src="{{ $projectdata->cover }}" alt="Текущий аватар {{ $projectdata->cover }}">
                @else
                    <p>Отсутствует.</p>
                @endif
            </div>
        </div>
        <select class="form-select mb-3" aria-label="" name="team">
            <option value="0" selected disabled>Дать доступ команде</option>
            {{-- @foreach ($collection as $item)
                <option value="false" selected>Дать доступ команде</option>
            @endforeach --}}
        </select>
        {{-- <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password">
            <label for="password">Пароль для подтверждения</label>
        </div> --}}

        <!-- Модаль -->
        <div class="modal fade" id="tagsModal" tabindex="-1" aria-labelledby="tagsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="tagsModalLabel">Отметьте необходимые теги</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Генерация списка тегов --}}
                        @foreach ($tags as $tag)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tag{{ $tag->id }}"
                                    name="tag-{{ $tag->id }}"
                                    {{ isset($selectedTags) ? $selectedTags[$tag->id] : null }}>
                                <label class="form-check-label" for="tag{{ $tag->id }}">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary m-auto">
            Сохранить
        </button>
    </form>
@endsection