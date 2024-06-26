@extends('layout')

@section('title')
    Регистрация
@endsection

@section('body')
    <form action="{{ route('signUp') }}" method="post" class="m-auto p-2 w-75" enctype="multipart/form-data">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" name="login" class="form-control" id="login" value="{{ old('login') ?? null }}">
            <label for="login">Логин</label>
        </div>
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email') ?? null }}">
            <label for="email">Email-адрес</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password">
            <label for="password">Пароль</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
            <label for="password_confirmation">Пароль</label>
        </div>
        <button type="submit" class="btn btn-primary">Регистрация</button>
    </form>
@endsection
