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
    <div>
        <div class="input-group">
            <span class="input-group-text">Поиск по названию:</span>
            <input type="text" class="form-control"id="search">
        </div>
    </div>
    <div class="row m-2">
        <b class="col">id</b>
        <b class="col">Название тега</b>
        <b class="col">Управление</b>
    </div>
    <div class="m-2 rounded border border-secondary shadow overflow-hidden">
        <div class="overflow-x-hidden overflow-y-scroll" style="max-height: 65vh">
            @foreach ($tags as $tag)
                <div class="searchable row p-2 border">
                    <b class="col">{{ $tag->id }}</b>
                    <div class="col criteria">{{ $tag->name }}</div>
                    <div class="col">
                        <a href="{{ route('tagDel', ['id' => $tag->id]) }}" class="btn btn-danger">
                            Удалить
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
