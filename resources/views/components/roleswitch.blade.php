@auth
    <div>
        <a href="{{ route('changeRole', ['role' => 1]) }}"
            class="btn btn-{{ auth()->user()->role_id !== 1 ? 'outline-' : null }}light">Пользователь</a>
        <a href="{{ route('changeRole', ['role' => 2]) }}"
            class="btn btn-{{ auth()->user()->role_id !== 2 ? 'outline-' : null }}success">Разработчик</a>
        <a href="{{ route('changeRole', ['role' => 3]) }}"
            class="btn btn-{{ auth()->user()->role_id !== 3 ? 'outline-' : null }}warning">Модератор</a>
        <a href="{{ route('changeRole', ['role' => 4]) }}"
            class="btn btn-{{ auth()->user()->role_id !== 4 ? 'outline-' : null }}danger">Администратор</a>
    </div>
@endauth
