@extends('layout')

@section('title')
    {{ $builddata->name }}
@endsection

@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
        <h2>
            {{ $builddata->name }}
        </h2>
        @if ($canedit && auth()->user()->role_id === 2 && !auth()->user()->banned)
            <div>
                <a href="{{ route('snapshotEditor', ['url' => $url, 'build' => $builddata->name]) }}"
                    class="mr-1 mb-1 btn btn-warning">Редактировать
                    информацию</a>
                <button class="mr-1 mb-1 btn btn-danger" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                    снапшот</button>
            </div>
            <!-- Модалька подтверждения -->
            <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                <form class="modal-dialog"
                    action="{{ route('snapshotDelete', ['url' => $url, 'build' => $builddata->name]) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                аккаунт?</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Это действие нельзя будет отменить. Все упоминания о этой версии на сайте исчезнут,
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
            </div>
        @endif
        <p class="mt-3 mb-3">
            {!! $builddata->description !!}
        </p>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Основной проект:
            </p>
            <i class="text-secondary d-block">
                <a href="{{ route('project', ['url' => $builddata->project_url]) }}">
                    {!! $builddata->project_name !!}
                </a>
            </i>
        </div>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Версия опубликована:
            </p>
            <span class="d-block">
                {!! $builddata->created_at_formatted !!}
            </span>
        </div>
        @if ($warning)
            <div class="d-flex flex-wrap justify-content-between alert alert-warning">
                <p class="text-secondary d-block">
                    Осторожно! Версия была отредактирована:
                </p>
                <span class="d-block">
                    {!! $builddata->updated_at_formatted !!}
                </span>
            </div>
        @endif
    </div>
@endsection
