@extends('layout')


@section('title')
    Редактирование пользователя
@endsection

@section('body')
    <div class="m-auto w-75 mt-3">
        <a href="{{ route('userpage', ['login'=>auth()->user()->login]) }}" class="d-block mt-3 btn btn-secondary">← Назад в личный кабинет</a>
    </div>
    <form action="{{ route('userSaveChanges') }}" method="POST" enctype="multipart/form-data" class="m-auto mt-3 w-75">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="text" name="login" class="form-control" id="login" placeholder="name@example.com"
                        value="{{ $userdata->login }}">
                    <label for="login">Никнейм</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="email" name="email" class="form-control" id="email" value="{{ $userdata->email }}">
                    <label for="email">Email</label>
                </div>
            </div>
        </div>
        <div class="form-floating mb-3">
            <textarea name="about" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! $userdata->about !!}</textarea>
            <label for="about">О вас</label>
            <div class="editor-buttons mt-3">
                <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="formFile" class="form-label">Аватар</label>
                <input class="form-control" type="file" id="formFile">
            </div>
            <div class="col">
                <label for="avatar">Текущий аватар:</label>
                @if ($userdata->avatar)
                    <img id="avatar" src="{{ $userdata->avatar }}" alt="Текущий аватар {{ $userdata->login }}">
                @else
                    <p>Отсутствует.</p>
                @endif
            </div>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password">
            <label for="password">Пароль для подтверждения</label>
        </div>
        <button class="btn btn-primary m-auto">
            Сохранить
        </button>
    </form>
@endsection
