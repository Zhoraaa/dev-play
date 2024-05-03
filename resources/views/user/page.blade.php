@extends('layout')

@if ($user_exist)
    @php
        $string = (isset(auth()->user()->id) && auth()->user()->id === $userdata->id) ? 'Личный кабинет' : $userdata->login;
        $canedit = (isset(auth()->user()->id) && auth()->user()->id === $userdata->id) ? true : false;
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
                    <a href="" class="btn btn-warning p-2">Редактировать информацию</a>
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
