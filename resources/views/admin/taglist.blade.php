@extends('layout')

@section('title')
    Админ панель: Список тегов
@endsection

@section('body')
    <form action="{{ route('tagNew') }}" method="post" class="m-2 rounded border overflow-hidden">
        @csrf
        <div class="input-group">
            <span class="input-group-text">Новый тег:</span>
            <input type="text" name="name" class="form-control" placeholder="Название...">
            <button class="btn btn-success">Добавить тег</button>
        </div>
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
                            <a href="{{ route('tagDel', ['id' => $tag->id]) }}" class="btn btn-danger">
                                Удалить
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
