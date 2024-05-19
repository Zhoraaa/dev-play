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

    @if (isset($devteams))
        {{-- Список команд --}}
        @foreach ($devteams as $devteam)
            <div class="w-75 m-auto mb-1 p-2 rounded border border-dark">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline mb-3">
                    <a href="{{ route('devteam', ['url' => $devteam->url]) }}" class="d-flex flex-wrap align-items-baseline">
                        @if (!empty($devteam->avatar))
                            <div class="avatar avatar-small" style="margin-right: 10px">
                                <img src="{{ asset('storage/imgs/teams/avatars/' . $devteam->avatar) }}" alt="">
                            </div>
                        @endif
                        <div>
                            <h5>
                                {{ $devteam->name }}
                            </h5>
                        </div>
                    </a>
                    <div>
                        <i class="text-secondary">
                            ({{ $devteam->formatted_created_at }})
                        </i>
                    </div>
                </div>
                <div class="mb-3">
                    <p>
                        {!! mb_substr($devteam->text, 0, 600) !!}
                    </p>
                </div>
            </div>
        @endforeach
    @endif

@endsection
