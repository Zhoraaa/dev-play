@extends('layout')

@php
    $canedit = isset(auth()->user()->id) && auth()->user()->id === $project->author_id ? true : false;
    $string = $canedit ? $project->name . ' - Панель разработчика' : $project->name;
    $taglist = '';
    foreach ($tags as $tag) {
        $taglist .=
            '<a href=\'/projects?tag-' .
            $tag->id .
            '=on\' class="link-primary link-primary-hover">' .
            $tag->name .
            '</a>, ';
    }
    $taglist = mb_substr($taglist, 0, -2) . '.';
@endphp

@section('title')
    {{ $string }}
@endsection

@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
        <h2>
            {{ $project->name }}
        </h2>
        <div>
            @if (!$canedit)
                @php
                    $substyle = !$subscribed ? 'success' : 'secondary';
                    $subtext = !$subscribed ? 'Подписаться' : 'Отписаться';
                    $title = !$subscribed
                        ? 'Подписавшись на обновления команды вы будете получать на почту уведомления об обновлениях проектов этого разработчика'
                        : 'Отказаться от подписки на обновления разработчика';
                @endphp
                <a href="{{ route('subscribe', ['type' => 'project', 'id' => $project->id]) }}"
                    class="btn btn-{{ $substyle }}" title="{{ $title }}">{{ $subtext }}</a>
            @endif
            @if ($canedit && auth()->user()->role_id === 2 && !auth()->user()->banned)
                <a href="{{ route('snapshotNew', ['url' => $project->url]) }}" class="mr-1 mb-1 btn btn-success">+
                    Новый
                    снапшот</a>
                <a href="{{ route('projectEditor', ['url' => $project->url]) }}"
                    class="mr-1 mb-1 btn btn-warning">Редактировать
                    информацию</a>
                <button class="mr-1 mb-1 btn btn-danger" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                    проект</button>
        </div>
        <!-- Модалька подтверждения -->
        <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
            <form class="modal-dialog" action="{{ route('projectDelete', ['url' => $project->url]) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                            аккаунт?</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Это действие нельзя будет отменить. Все упоминания о проекте на сайте исчезнут,
                        некоторая
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
            @endif
        </div>
        <p class="mt-3 mb-3">
            {!! $project->description !!}
        </p>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Теги:
            </p>
            <i class="text-secondary d-block">
                {!! $taglist !!}
            </i>
        </div>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Проект создан:
            </p>
            <span class="d-block">
                {!! $project->created_at_formatted !!}
            </span>
        </div>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Последнее обновление:
            </p>
            <span class="d-block">
                {!! $project->updated_at_formatted !!}
            </span>
        </div>
    </div>

    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
        <h5>
            Версии по новизне
        </h5>
        @foreach ($snapshots as $snapshot)
            <a href="{{ route('snapshot', ['url' => $url, 'build' => $snapshot->name]) }}">{{ $snapshot->name }}</a><br>
        @endforeach
    </div>
@endsection
