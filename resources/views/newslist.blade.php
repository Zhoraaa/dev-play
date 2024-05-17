@extends('layout')

@section('title')
    Лента новостей
@endsection

@section('body')
    <!-- Button trigger modal -->
    <div class="w-75 m-auto mt-2 mb-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postEditorModal">
            Новый пост
        </button>
    </div>

    <!-- Modal -->
    <form action="{{ route('postSave') }}" method="post" class="modal fade" id="postEditorModal" tabindex="-1"
        aria-labelledby="postEditorModalLabel" aria-hidden="true">
        @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="postEditorModalLabel">Написать пост</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <textarea name="text" id="editor" style="min-height: 130px; resize: none" class="form-control" id="about">{!! $projectdata->description ?? null !!}</textarea>
                        <label for="text">Давным-давно...</label>
                        <div class="editor-buttons mt-3">
                            <button type="button" id="boldBtn" class="btn btn-outline-secondary"><b>Жирный</b></button>
                            <button type="button" id="italicBtn" class="btn btn-outline-secondary"><i>Курсив</i></button>
                            <button type="button" id="linkBtn" class="btn btn-outline-secondary">Вставить ссылку</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="filesMultiple" class="form-label">Изображения</label>
                        <input class="form-control" type="file" id="filesMultiple" name="images" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скрыть</button>
                    <button class="btn btn-success">Опубликовать</button>
                </div>
            </div>
        </div>
    </form>

    @foreach ($news as $post)
        <div class="w-75 m-auto mb-1 p-2 rounded border border-dark">
            <div class="d-flex flex-wrap justify-content-between">
                <a href="{{ route('userpage', ['login' => $post->author]) }}">
                    <h5>
                        {{ $post->author }}
                    </h5>
                </a>
                @auth
                    @if ($post->author_id === auth()->user()->id)
                        <div>
                            <a href="{{ route('postDel', ['id' => $post->id]) }}" class="btn btn-outline-danger">
                                Удалить пост
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
            <a href="" class="text-secondary">
                <i>
                    ({{ $post->formatted_created_at }})
                </i>
            </a>
            <p>
                {!! $post->text !!}
            </p>
        </div>
    @endforeach
@endsection
