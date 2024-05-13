@extends('layout')

@if ($project_exist)
    @php
        $string =
            isset(auth()->user()->id) && auth()->user()->id === $projectdata->id
                ? 'Личный кабинет'
                : $projectdata->name;
        $canedit = isset(auth()->user()->id) && auth()->user()->id === $projectdata->author_id ? true : false;
    @endphp
@else
    @php
        $string = 'Пользователь не найден';
    @endphp
@endif

@section('title')
    {{ $string }}
@endsection

@if ($project_exist)
    @section('body')
        <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
            <div class="d-flex justify-content-between mb-2">
                <h2>
                    {{ $projectdata->name }}
                </h2>
                @if ($canedit)
                    <div>
                        <a href="{{ route('snapshotNew', ['project' => $projectdata->url]) }}" class="btn btn-success">+ Новый
                            снапшот</a>
                        <a href="{{ route('projectEditor', ['url' => $projectdata->url]) }}"
                            class="btn btn-warning">Редактировать информацию</a>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                            проект</button>
                    </div>
                    <!-- Модалька подтверждения -->
                    <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel"
                        aria-hidden="true">
                        <form class="modal-dialog" action="{{ route('projectDelete', ['url' => $projectdata->url]) }}"
                            method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                        аккаунт?</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
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
                    </div>
                @endif
            </div>
            <div class="d-flex justify-content-between">
                <p class="text-secondary">
                    Проект создан:
                </p>
                <span>
                    {!! $projectdata->created_at_formatted !!}
                </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <p class="text-secondary">
                    Последнее обновление:
                </p>
                <span>
                    {!! $projectdata->updated_at_formatted !!}
                </span>
            </div>
            <p>
                {!! $projectdata->description !!}
            </p>
        </div>
    @endsection
@endif
