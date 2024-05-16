@extends('layout')

@section('title')
    Админ панель: Список Пользователей
@endsection

@section('body')
    <div class="m-2 rounded border border-secondary shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">Имя пользователя</th>
                    <th scope="col">Уровень доступа</th>
                    <th scope="col">Управление</th>
                </tr>
            </thead>
            <tbody class="overflow-y-scroll">
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $user->id }}</th>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
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
                            @else
                                <a href="{{ route('userEdit', ['id' => $user->id, 'ban' => false]) }}"
                                    class="btn btn-outline-warning">
                                    Разбанить
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
