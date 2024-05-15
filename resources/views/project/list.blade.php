@extends('layout')

@section('title')
    Проекты
@endsection

@section('body')
    <div class="d-flex">
        <div class="w-50 overflow-y-scroll">
            @foreach ($projects as $project)
                <div class="m-2 p-3 rounded border border-secondary shadow-lg">
                    <a href="{{ route('project', ['url' => $project->url]) }}" class="text-decoration-none text-black">
                        <h3>
                            {{ $project->name }}
                        </h3>
                    </a>
                    <p class="text-secondary">
                        <i>
                            За авторством:
                            @if ($project->team_rights_id)
                                <a
                                    href="{{ route('userpage', ['login' => $project->dev_team]) }}">{{ $project->dev_team }}</a>
                            @else
                                @if ($project->author)
                                    <a
                                        href="{{ route('userpage', ['login' => $project->author]) }}">{{ $project->author }}</a>
                                @else
                                    <i class="text-secondary">Автор удалил свою страницу</i>
                                @endif
                            @endif
                        </i>
                    </p>
                    <p>
                        {!! $project->description !!}
                    </p>
                </div>
            @endforeach
        </div>
        <div class="w-50 m-2 p-3 rounded border border-secondary shadow-lg">

        </div>
    </div>
@endsection
