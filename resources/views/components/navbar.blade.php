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
        @guest
            <a href="{{ route('auth') }}" class="mr-2 btn btn-outline-primary">Вход</a>
            <a href="{{ route('reg') }}" class="btn btn-outline-primary">Регистрация</a>
        @endguest
        @auth
            <a href="{{ route('signOut') }}" class="btn btn-outline-danger">Выйти</a>
        @endauth
    </div>
</div>
