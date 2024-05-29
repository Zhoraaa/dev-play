@extends('layout')

@section('title')
    {{ $snapshot->name }}
@endsection

@section('body')
    <div class="m-auto mt-3 p-3 w-75 rounded border border-secondary">
        <h2>
            {{ $snapshot->name }}
        </h2>
        @if ($canedit && auth()->user()->role_id === 2 && !auth()->user()->banned)
            <div>
                <a href="{{ route('snapshotEditor', ['url' => $url, 'build' => $snapshot->name]) }}"
                    class="mr-1 mb-1 btn btn-warning">Редактировать
                    информацию</a>
                <button class="mr-1 mb-1 btn btn-danger" data-bs-toggle="modal" data-bs-target="#areYouSure">Удалить
                    версию</button>
            </div>
            <!-- Модалька подтверждения -->
            <div class="modal fade" id="areYouSure" tabindex="-1" aria-labelledby="areYouSureLabel" aria-hidden="true">
                <form class="modal-dialog"
                    action="{{ route('snapshotDelete', ['url' => $url, 'build' => $snapshot->name]) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="areYouSureLabel">Вы действительно хотите удалить
                                эту версию?</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Это действие нельзя будет отменить. Все упоминания о этой версии на сайте исчезнут,
                            некоторая информация будет безвозвратно утрачена.
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
            {!! $snapshot->description !!}
        </p>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Основной проект:
            </p>
            <i class="text-secondary d-block">
                <a href="{{ route('project', ['url' => $snapshot->project_url]) }}">
                    {!! $snapshot->project_name !!}
                </a>
            </i>
        </div>
        <div class="d-flex flex-wrap justify-content-between">
            <p class="text-secondary d-block">
                Версия опубликована:
            </p>
            <span class="d-block">
                {!! $snapshot->formatted_created_at !!}
            </span>
        </div>

        <div class="row">
            @if ($downloadable->all())
                <div class="col">
                    <h5>
                        Файлы для загрузки:
                    </h5>
                    @foreach ($downloadable as $downloadable_file)
                        {{-- @dd('storage/snapshots/downloadable/' . $downloadable_file->file_name) --}}
                        <a href="{{ route('download', ['file' => $downloadable_file->file_name]) }}" target="_blank"
                            rel="noopener noreferrer">
                            {{ explode('_', $downloadable_file->file_name)[4] }}
                        </a>
                    @endforeach
                </div>
            @endif
            @if ($medias)
                {{-- Триггер модальки с медиа --}}
                <div class="col" data-bs-toggle="modal" data-bs-target="#mediaFiles">
                    <h5>
                        Файлы для просмотра:
                    </h5>
                    <div>
                        @foreach ($medias as $media)
                            <img src="{{ asset('storage/snapshots/media/' . $media['file_name']) }}" class="shadow-sm m-1"
                                alt="{{ $media['file_name'] }}" style="height: 100px; cursor: pointer">
                        @endforeach
                    </div>
                </div>

                {{-- Модаль с медиа --}}
                <div class="modal modal-xl fade" id="mediaFiles" tabindex="-1" aria-labelledby="mediaFilesLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="mediaFilesLabel">Медиафайлы</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Закрыть"></button>
                            </div>
                            <div class="modal-body">
                                {{-- Вывод медиа --}}
                                <div id="carouselExample" class="carousel slide">
                                    <div class="carousel-inner rounded border overflow-hidden">
                                        {{-- Генерация слайдера с картинками --}}
                                        @foreach ($medias as $key => $media)
                                            <div class="carousel-item {{ $key === 0 ? 'active' : null }}">
                                                <div class="w-100 carousel-img-wrapper">
                                                    <img src="{{ asset('storage/snapshots/media/' . $media['file_name']) }}"
                                                        class="d-block shadow" alt="{{ $media['file_name'] }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample"
                                        data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Предыдущий</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample"
                                        data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Следующий</span>
                                    </button>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
