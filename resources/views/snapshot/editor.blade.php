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
                        value="{{ old('name') ?? ($builddata->name ?? null) }}">
                    <label for="name">Название снапшота</label>
                </div>
            </div>
            <button type="button" class="btn btn-success col mt-3" data-bs-toggle="modal" data-bs-target="#mediaModal">
                Редактировать изображения
            </button>
        </div>
        <div class="form-floating mb-3">
            <textarea name="description" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! old('description') ?? ($builddata->description ?? null) !!}</textarea>
            <label for="description">Описание снапшота</label>
            <div class="editor-buttons mt-3">
                <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col mb-3">
                <label for="filesMultiple" class="form-label">Изображения</label>
                <input class="form-control" type="file" id="filesMultiple" name="images" multiple>
            </div>
            <div class="col mb-3">
                <label for="filesMultiple" class="form-label">Загружаемые файлы</label>
                <input class="form-control" type="file" id="filesMultiple" name="downloadable" multiple>
            </div>
        </div>

        <!-- Модаль -->
        <div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="mediaModalLabel">Изображения снапшота</h1>
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
