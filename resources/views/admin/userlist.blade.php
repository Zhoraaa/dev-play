@extends('layout')

@section('title')
    Админ панель: Список Пользователей
@endsection

@section('body')
    <div class="m-2">
        <div class="input-group">
            <span class="input-group-text">Поиск по имени пользователя:</span>
            <input type="text" class="form-control"id="search">
        </div>
    </div>
    <div class="m-2 rounded border border-secondary shadow overflow-hidden">
        <div class="row p-2">
            <b class="col">id</b>
            <b class="col">Имя пользователя</b>
            <b class="col">Уровень доступа</b>
            <b class="col">Управление</b>
        </div>
        <div class="overflow-y-scroll overflow-x-hidden" style="max-height: 70vh">
            @foreach ($users as $user)
                <div class="row p-2 border searchable">
                    <b class="col">{{ $user->id }}</b>
                    <a href="{{ route('user', ['login' => $user->login]) }}" class="col criteria">{{ $user->login }}</a>
                    <div class="col">{{ $user->role }}</div>
                    <div class="col">
                        @if (!$user->banned)
                            @switch($user->role_id)
                                @case(1)
                                    <a href="{{ route('userEdit', ['id' => $user->id, 'changeRole' => 3]) }}"
                                        class="btn btn-outline-warning">
                                        Сделать модератором
                                    </a>
                                @break

                                @case(3)
                                    <a href="{{ route('userEdit', ['id' => $user->id, 'changeRole' => 1]) }}"
                                        class="btn btn-outline-warning">
                                        Снять с модерки
                                    </a>
                                @break

                                @default
                            @endswitch
                            @if ($user->role_id !== 4)
                                <a href="{{ route('userEdit', ['id' => $user->id, 'ban' => true]) }}"
                                    class="btn btn-outline-danger">
                                    Забанить
                                </a>
                            @endif
                        @elseif (auth()->user()->id != $user->id)
                            <a href="{{ route('userEdit', ['id' => $user->id, 'ban' => false]) }}"
                                class="btn btn-outline-warning">
                                Разбанить
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
            </d>
        </div>
    @endsection
