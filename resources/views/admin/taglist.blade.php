@extends('layout')

@section('title')
    Админ панель: Список тегов
@endsection

@section('body')
    <form action="" method="post" class="input-group m-2 rounded border overflow-hidden">
        @csrf
        <span class="input-group-text">Новый тег:</span>
        <input type="text" class="form-control" placeholder="Название...">
        <button class="btn btn-success">Добавить тег</button>
    </form>
    <div class="m-2 rounded border border-secondary shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">Название тега</th>
                    <th scope="col">Управление</th>
                </tr>
            </thead>
            <tbody class="overflow-y-scroll">
                @foreach ($tags as $tag)
                    <tr>
                        <th scope="row">{{ $tag->id }}</th>
                        <td>{{ $tag->name }}</td>
                        <td>
                            <a href="" class="btn btn-warning">
                                Редактировать
                            </a>
                            <a href="" class="btn btn-danger">
                                Удалить
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
