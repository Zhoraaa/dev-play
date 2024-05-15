@extends('layout')


@section('title')
    Редактор снапшотов
@endsection

@section('body')
    <div class="m-auto w-75 mt-3">
        <a href="{{ URL::previous() }}" class="d-block mt-3 btn btn-secondary">← Назад</a>
    </div>
    {{-- Редактирование --}}
    <form action="{{ route('snapshotSaveChanges', ['url' => $url]) }}" method="POST" enctype="multipart/form-data"
        class="m-auto mt-3 w-75">
        @csrf
        @if (isset($builddata))
            <input type="hidden" value="{{ $builddata->id ?? null }}" name="id">
        @endif
        <div class="row mb-3">
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="name"
                        value="{{ $builddata->name ?? null }}">
                    <label for="name">Название</label>
                </div>
            </div>
                <button class="btn btn-success col mt-3">
                    Редактировать изображения
                </button>
        </div>
        <div class="form-floating mb-3">
            <textarea name="description" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! $builddata->description ?? null !!}</textarea>
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
                @if (isset($builddata->cover))
                    <img id="avatar" src="{{ $builddata->cover }}" alt="Текущий аватар {{ $builddata->cover }}">
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
        <div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="mediaModalLabel">Отметьте необходимые теги</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Генерация списка тегов --}}
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
