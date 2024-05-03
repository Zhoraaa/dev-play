@extends('layout')

@section('title')
    Регистрация
@endsection

@section('body')
    <form action="{{ route('signIn') }}" method="post" class="m-auto p-2 w-75" enctype="multipart/form-data">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" name="login" class="form-control" id="login" placeholder="User123">
            <label for="login">Логин</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="password">
            <label for="password">Пароль</label>
        </div>
        <button type="submit" class="btn btn-primary">Вход</button>
    </form>
@endsection
