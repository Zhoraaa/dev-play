@extends('layout')

@if ($user_exist)
    @php
        $string =
            isset(auth()->user()->id) && auth()->user()->id === $userdata->id ? 'Личный кабинет' : $userdata->login;
        $canedit = isset(auth()->user()->id) && auth()->user()->id === $userdata->id ? true : false;
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
        <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
            <div class="d-flex justify-content-between mb-2">
                <h2>
                    {{ $userdata->login }}
                </h2>
                @if ($canedit)
                    <a href="" class="btn btn-warning p-2 pr-3 pl-3">Редактировать информацию</a>
                    <button class="btn btn-danger p-2 pr-3 pl-3" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                        аккаунт</button>
                    <!-- Модалька подтверждения -->
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
                                    <button type="submit" class="btn btn-danger">Да</ф>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
            <div class="d-flex justify-content-between mb-2">
                <p class="text-secondary">
                    На сайте с:
                </p>
                <span>
                    {{ $userdata->created_at }}
                </span>
            </div>
        </div>
    @endsection
@endif
