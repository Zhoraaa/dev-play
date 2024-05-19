@extends('layout')

@section('title')
    {{ $team->name }}
@endsection
@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary {{ $team->banned ? 'alert alert-danger' : null }}">
        <div class="avatar avatar-big">
            <img src="{{ asset('storage/imgs/users/avatars/' . $team->avatar) }}" alt="">
        </div>
        <div class="d-flex flex-wrap justify-content-between mb-2">

            <h2 class="text-primary">
                {{ $team->name }}
                @if ($team->banned)
                    <i class="text-secondary">
                        (Пользователь забанен)
                    </i>
                @endif
            </h2>
            @if ($canedit)
                <div>
                    @if ($canedit >= 2)
                        <a href="{{ route('projectNew') }}" class="btn btn-success mb-1">+ Новый проект</a>
                    @endif
                    {{-- Триггер модальки настройки аватарки --}}
                    <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#avatarModal">Настройка
                        аватара</button>
                    @if ($canedit === 3)
                        <a href="{{ route('devteamEditor', ['url' => $team->url]) }}"
                            class="btn btn-warning mb-1">Редактировать информацию</a>
                        {{-- Триггер модальки удаления --}}
                        <button class="btn btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                            команду</button>
                    @endif
                </div>
                {{-- Модалька обновления аватарки --}}
                <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('avatarUpdate') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="avatarModalLabel">Обновить аватар</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="formFile" class="form-label">Аватар</label>
                                        <input class="form-control" type="file" id="formFile" name="avatar">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Обновить аватар</button>
                                    <a href="{{ route('avatarDelete', ['login' => auth()->user()->login]) }}"
                                        class="btn btn-danger">Удалить аватар</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- Модалька подтверждения удаления команды --}}
                <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                    <form class="modal-dialog" action="{{ route('userdelete') }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                    аккаунт?</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Это действие нельзя будет отменить. Все упоминания о Вас на сайте исчезнут, некоторая
                                информация будет безвозвратно утрачена.
                                <div class="form-floating mt-3">
                                    <input type="password" name="password" class="form-control" id="floatingPassword"
                                        placeholder="Password">
                                    <label for="floatingPassword">Для подтверждения введите пароль.</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Нет</button>
                                <button type="submit" class="btn btn-danger">Да</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
        <div class="d-flex flex-wrap justify-content-between mb-3">
            <span class="text-secondary">
                {{ $team->role }} зарегестрирован...
            </span>
            <span>
                {!! $team->created_at !!}
            </span>
        </div>
        <p>
            {!! $team->about !!}
        </p>
    </div>
@endsection