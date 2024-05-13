<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">@yield('title')</a>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu"
            aria-controls="offcanvasMenu">Меню</button>
    </div>
</nav>

<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasMenu"
    aria-labelledby="offcanvasMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasMenuLabel">Меню</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        {{-- Ссылки на страницы сайта --}}
        <a href="{{ route('home') }}" class="m-1 btn btn-outline-primary">На главную</a>
        <a href="{{ route('news') }}" class="m-1 btn btn-outline-primary">Новости</a>
        <a href="{{ route('projects') }}" class="m-1 btn btn-outline-primary">Проекты</a>
        <a href="{{ route('devTeams') }}" class="m-1 btn btn-outline-primary">Команды разработчиков</a>
        <hr>
        {{-- Ссылки пользователей --}}
        <a href="{{ route('userpage', ['login' => auth()->user()->login]) }}"
            class="m-1 btn btn-outline-primary">Личный кабинет</a>
        @auth
            @if (auth()->user()->role_id == 1)
                <button class="m-1 btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#beDeveloper">Стать
                    разработчиком</button>

                {{-- Модалька --}}
                <div class="modal fade" id="beDeveloper" tabindex="-1" aria-labelledby="beDeveloperLabel"
                    aria-hidden="true" data-bs-backdrop="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="beDeveloperLabel">Стать разработчиком</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Для того, чтобы стать разработчиком, вам необходимо полностью заполнить свой профиль.
                                Добавить аватар, описание, не быть забаненным и подтвердить почту.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="button" class="btn btn-primary">Далее</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (auth()->user()->role_id == 2)
                <a href="{{ route('projects', ['developer' => auth()->user()->login]) }}"
                    class="m-1 btn btn-outline-primary">Мои проекты</a>
                <a href="{{ route('devTeams', ['member' => auth()->user()->login]) }}"
                    class="m-1 btn btn-outline-primary">Мои
                    команды</a>
            @endif
            @if (auth()->user()->role_id >= 3)
                <a href="{{ route('userList') }}" class="m-1 btn btn-outline-primary">Пользователи</a>
                <a href="{{ route('tagList') }}" class="m-1 btn btn-outline-primary">Список тегов</a>
            @endif
            <hr>
        @endauth
        {{-- Авторизация и выход --}}
        @guest
            <a href="{{ route('auth') }}" class="m-1 btn btn-outline-primary">Вход</a>
            <a href="{{ route('reg') }}" class="m-1 btn btn-outline-primary">Регистрация</a>
        @endguest
        @auth
            <a href="{{ route('signOut') }}" class="m-1 btn btn-outline-danger">Выйти</a>
        @endauth
    </div>
</div>
