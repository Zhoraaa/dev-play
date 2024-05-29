@extends('layout')

@section('title')
    Разработчики
@endsection

@section('body')
    <div class="m-auto mb-2 w-75">
        <div class="input-group">
            <span class="input-group-text">Поиск по никнейму:</span>
            <input type="text" class="form-control"id="search">
        </div>
        <hr>
    </div>

    @if (isset($devs))
        <div class="d-flex flex-wrap justify-content-center">
            {{-- Список разработчиков --}}
            @foreach ($devs as $dev)
                <div class="m-1 p-2 rounded border searchable" style="max-width: 325px; height:120px">
                    <div class="d-flex flex-wrap justify-content-between align-items-baseline">
                        <a href="{{ route('user', ['login' => $dev->login]) }}"
                            class="d-flex flex-wrap align-items-baseline mb-2">
                            @if (!empty($dev->avatar))
                                <div class="avatar rounded-circle avatar-medium" style="margin-right: 10px">
                                    <img src="{{ asset('storage/imgs/users/avatars/' . $dev->avatar) }}" alt="">
                                </div>
                            @endif
                            <div>
                                <h4 class="criteria">
                                    {{ $dev->login }}
                                </h4>
                            </div>
                        </a>
                    </div>
                    <div class="mb-3">
                        <p>
                            {!! mb_strlen($dev->about) > 150 ? mb_substr(strip_tags($dev->about), 0, 150) . '...' : $dev->about !!}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
