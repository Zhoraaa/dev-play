@extends('layout')

@section('title')
    Лента новостей
@endsection

@section('body')
    @foreach ($news as $post)
        <div class="w-75 m-auto mb-1 p-2 rounded border border-dark">
            <div class="d-flex flex-wrap justify-content-between">
                <a href="{{ route('userpage', ['login' => $post->author]) }}">
                    <h5>
                        {{ $post->author }}
                    </h5>
                </a>
                <i class="text-secondary">
                    {{$post->formatted_created_at}}
                </i>
            </div>
            <p>
                {{ $post->text }}
            </p>
        </div>
    @endforeach
@endsection
