@extends('layout')

@section('title')
    Команды разработки
@endsection

@section('body')
    @auth
        @if (auth()->user()->role_id === 2)
            <div class="w-75 m-auto mt-2 mb-2">
                <a href="{{ route('devteamNew') }}" class="btn btn-primary">
                    Создать команду
                </a>
            </div>
        @endif
    @endauth

    <div class="m-auto mb-2 w-75">
        <div class="input-group">
            <span class="input-group-text">Поиск по названию:</span>
            <input type="text" class="form-control"id="search">
        </div>
        <hr>
    </div>

    @if (isset($devteams))
        {{-- Список команд --}}
        @foreach ($devteams as $devteam)
            <div class="w-75 m-auto mb-1 p-2 rounded border searchable">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline">
                    <a href="{{ route('devteam', ['url' => $devteam->url]) }}"
                        class="d-flex flex-wrap align-items-baseline">
                        @if (!empty($devteam->avatar))
                            <div class="avatar rounded-circle avatar-small" style="margin-right: 10px">
                                <img src="{{ asset('storage/imgs/teams/avatars/' . $devteam->avatar) }}" alt="">
                            </div>
                        @endif
                        <div>
                            <h5 class="criteria">
                                {{ $devteam->name }}
                            </h5>
                        </div>
                    </a>
                </div>
                <div class="mb-2 d-flex flex-wrap justify-content-between">
                    <div class="text-secondary">
                        Команда сформирована
                    </div>
                    <div>
                        {!! $devteam->formatted_created_at !!}
                    </div>
                </div>
                <div class="mb-3">
                    <p>
                        {!! mb_substr($devteam->description, 0, 600) !!}
                    </p>
                </div>
            </div>
        @endforeach
    @else
    <i class="text-secondary">
        На текущий момент нет ни одной команды разработчиков.
    </i>
    @endif

@endsection
