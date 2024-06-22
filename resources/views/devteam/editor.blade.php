@extends('layout')


@section('title')
    Редактирование команды
@endsection

@section('body')
    <div class="m-auto w-75 mt-3">
        <a href="{{ URL::previous() }}" class="d-block mt-3 btn btn-secondary">← Назад</a>
    </div>
    {{-- Редактирование --}}
    <form action="{{ route('devteamSave') }}" method="POST" enctype="multipart/form-data" class="m-auto mt-3 w-75">
        @csrf
        @if (isset($team))
            <input type="hidden" value="{{ $team->id ?? null }}" name="id">
        @endif
        <div class="row mb-3">
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="name"
                        value="{{ old('name') ?? ($team->name ?? null) }}">
                    <label for="name">Название команды</label>
                </div>
            </div>
            <div class="col mt-3">
                <div class="form-floating">
                    <input type="text" name="url" class="form-control" id="url"
                        value="{{ old('url') ?? ($team->url ?? null) }}">
                    <label for="url">URL</label>
                    <small class="text-secondary"><i>Ссылка на команду, например exampleTeam</i></small>
                </div>
            </div>
        </div>
        <div class="form-floating mb-3">
            <textarea name="description" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! old('description') ?? ($team->description ?? null) !!}</textarea>
            <label for="description">Описание вашей команды</label>
            <div class="editor-buttons mt-3">
                <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
            </div>
        </div>

        <button class="btn btn-primary m-auto">
            Сохранить
        </button>
    </form>
@endsection
