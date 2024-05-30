@extends('layout')


@section('title')
    Редактор версий
@endsection

@section('body')
    <div class="m-auto w-75 mt-3">
        <a href="{{ URL::previous() }}" class="d-block mt-3 btn btn-secondary">← Назад</a>
    </div>
    {{-- Редактирование --}}
    <form action="{{ route('snapshotSaveChanges', ['url' => $url]) }}" method="POST" enctype="multipart/form-data"
        class="m-auto mt-3 w-75">
        @csrf
        @if (isset($snapshot))
            <input type="hidden" value="{{ $snapshot->id ?? null }}" name="id">
        @endif
        <div class="row mb-3">
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="name"
                        value="{{ old('name') ?? ($snapshot->name ?? null) }}">
                    <label for="name">Название версии</label>
                </div>
            </div>
        </div>
        <div class="form-floating mb-3">
            <textarea name="description" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! old('description') ?? ($snapshot->description ?? null) !!}</textarea>
            <label for="description">Описание версии</label>
            <div class="editor-buttons mt-3">
                <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col mb-3">
                <label for="media" class="form-label">Изображения</label>
                <input class="form-control" type="file" id="media" name="images[]" multiple>
            </div>
            <div class="col mb-3">
                <label for="downloadable" class="form-label">Загружаемые файлы</label>
                <input class="form-control" type="file" id="downloadable" name="downloadable[]" multiple>
            </div>
        </div>

        <button class="btn btn-primary m-auto">
            Сохранить
        </button>
    </form>
@endsection
