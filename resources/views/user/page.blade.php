@extends('layout')

@if ($user_exist)
    @php
        $string =
            isset(auth()->user()->id) && auth()->user()->id === $user->id ? 'Личный кабинет' : $user->login;
        $canedit = isset(auth()->user()->id) && auth()->user()->id === $user->id ? true : false;

        switch ($user->role_id) {
            case 2:
                $namestyle = 'success';
                break;
            case 3:
                $namestyle = 'warning';
                break;
            case 4:
                $namestyle = 'danger';
                break;

            default:
                $namestyle = 'black';
                break;
        }
    @endphp
@else
    @php
        $string = 'Пользователь не найден';
    @endphp
@endif

@section('title')
    {{ $string }}
@endsection

@if ($user_exist)
    @section('body')
        <div
            class="m-auto mt-3 p-3 w-75 rounded border border-secondary {{ $user->banned ? 'alert alert-danger' : null }}">
            @if ($user->avatar)
                <div class="avatar avatar-big">
                    <img src="{{ asset('storage/imgs/teams/avatars/' . $user->avatar) }}" alt="">
                </div>
            @endif
            <div class="d-flex flex-wrap justify-content-between mb-2">

                <h2 class="text-{{ $namestyle }}">
                    {{ $user->login }}
                    @if ($user->banned)
                        <i class="text-secondary">
                            (Пользователь забанен)
                        </i>
                    @endif
                </h2>
                <div>
                    @if (auth()->user()->id != $user->id && $user->role_id === 2)
                        @php
                            $substyle = !$subscribed ? 'success' : 'secondary';
                            $subtext = !$subscribed ? 'Подписаться' : 'Отписаться';
                            $title = !$subscribed
                                ? 'Подписавшись на обновления команды вы будете получать на почту уведомления об обновлениях проектов этого разработчика'
                                : 'Отказаться от подписки на обновления разработчика';
                        @endphp
                        <a href="{{ route('subscribe', ['type' => 'developer', 'id' => $user->id]) }}"
                            class="btn btn-{{ $substyle }}" title="{{ $title }}">{{ $subtext }}</a>
                    @endif
                    @if ($canedit)
                        @if (auth()->user()->role_id === 2 && !auth()->user()->banned)
                            <a href="{{ route('projectNew') }}" class="btn btn-success mb-1">+ Новый проект</a>
                        @endif
                        {{-- Обновление аватарки --}}
                        <button class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#avatarModal">Настройка
                            аватара</button>
                        <a href="{{ route('userEditor', ['login' => auth()->user()->login]) }}"
                            class="btn btn-warning mb-1">Редактировать информацию</a>
                        <button class="btn btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                            аккаунт</button>
                        {{-- Модалька обновления аватарки --}}
                        <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('avatarUpdate') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="avatarModalLabel">Обновить аватар</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="formFile"
                                                        name="avatar">
                                                    <small class="text-secondary"><i>Старые аватарки не
                                                            сохраняются;</i></small><br>
                                                    <small class="text-secondary"><i>Поддерживаемые форматы: .jpg,
                                                            .jpeg, .png, .gif;</i></small>
                                                </div>
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
                        {{-- Модалька подтверждения --}}
                        <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel"
                            aria-hidden="true">
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
                                        Это действие нельзя будет отменить. Все упоминания о Вас на сайте исчезнут,
                                        некоторая
                                        информация будет безвозвратно утрачена.
                                        <div class="form-floating mt-3">
                                            <input type="password" name="password" class="form-control"
                                                id="floatingPassword" placeholder="Password">
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
            </div>
            <div class="d-flex flex-wrap justify-content-between mb-3">
                <span class="text-secondary">
                    {{ $user->role }} зарегестрирован...
                </span>
                <span>
                    {!! $user->created_at !!}
                </span>
            </div>
            <p>
                {!! $user->about !!}
            </p>
        </div>
    @endsection
@endif
