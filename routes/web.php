<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DevTeamController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SnapshotsController;
use App\Http\Controllers\SubscribesController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/role-switch/{role}', function ($role) {
    Auth::user()->update([
        'role_id' => $role
    ]);
    return redirect()->back();
})->middleware('auth')->name('changeRole');

// Конкретные страницы
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/projects', [PageController::class, 'projects'])->name('projects');
Route::get('/devteams', [PageController::class, 'devTeams'])->name('devTeams');
Route::get('/rules/publication', function () {
    return view('rules.publication');
})->name('publicationRules');

// Пользователь
// Регистрация / Авторизация / Выход
Route::get('/auth', function () {
    return view('user.auth');
})->name('auth');
Route::get('/reg', function () {
    return view('user.reg');
})->name('reg');
Route::post('/signUp', [UserController::class, 'create'])->middleware('guest')->name('signUp');
Route::post('/login', [UserController::class, 'login'])->middleware('guest')->name('signIn');
Route::get('/signOut', [UserController::class, 'logout'])->middleware('auth')->name('signOut');
// Профиль пользователя и редактирование данных пользователя
Route::get('/user/{login}', [UserController::class, 'index'])->name('userpage');
Route::post('/user/delete', [UserController::class, 'destroy'])->middleware('auth')->name('userdelete');
Route::get('/user/{login}/edit', [UserController::class, 'editor'])->middleware('auth')->name('userEditor');
Route::post('/user/save', [UserController::class, 'update'])->middleware('auth')->name('userSaveChanges');
// Обновление аватара
Route::post('/user/new-avatar', [UserController::class, 'avatarUpdate'])->middleware('auth')->name('avatarUpdate');
Route::get('/user/{login}/del-avatar', [UserController::class, 'avatarDelete'])->middleware('auth')->name('avatarDelete');
// Становление разработчиком
Route::get('/user/{login}/beDeveloper', [UserController::class, 'beDeveloper'])->middleware('auth')->name('beDeveloper');

// Команды разработчиков
Route::get('/new-devteam', function () {
    $devs = User::where('role_id', '=', 2)
        ->where('id', '!=', Auth::user()->id)
        ->orderBy('login', 'asc')->get();

    return view('devteam.editor', ['devs' => $devs]);
})->middleware('auth')->name('devteamNew');
Route::get('/team/{url}', [DevTeamController::class, 'index'])->name('devteam');
Route::post('/team/save', [DevTeamController::class, 'save'])->middleware('auth')->name('devteamSave');
Route::get('/team/{url}/edit', [DevTeamController::class, 'editor'])->middleware('auth')->name('devteamEditor');
Route::post('/team/{url}/delete', [DevTeamController::class, 'destroy'])->middleware('auth')->name('devteamDelete');

// Проекты
Route::get('/new-project', function () {
    $tags = Tag::orderBy('name', 'asc')->get();
    return view('project.editor', ['tags' => $tags]);
})->middleware('auth')->name('projectNew');
Route::get('/project/{url}', [ProjectController::class, 'index'])->name('project');
Route::post('/project/save', [ProjectController::class, 'save'])->middleware('auth')->name('projectSaveChanges');
Route::get('/project/{url}/edit', [ProjectController::class, 'editor'])->middleware('auth')->name('projectEditor');
Route::post('/project/{url}/delete', [ProjectController::class, 'destroy'])->middleware('auth')->name('projectDelete');
// Снапшоты
Route::get('/project/{url}/new-snapshot', function ($url) {
    return view('snapshot.editor', ['url' => $url]);
})->middleware('auth')->name('snapshotNew');
Route::get('/project/{url}/snapshot/{build}/', [SnapshotsController::class, 'index'])->middleware('auth')->name('snapshot');
Route::post('/project/{url}/snapshot/save', [SnapshotsController::class, 'save'])->middleware('auth')->name('snapshotSaveChanges');
Route::get('/project/{url}/snapshot/{build}/edit', [SnapshotsController::class, 'editor'])->middleware('auth')->name('snapshotEditor');
Route::post('/project/{url}/snapshot/{build}/delete', [SnapshotsController::class, 'destroy'])->middleware('auth')->name('snapshotDelete');

// Посты
Route::post('/post-save/{from_team}/{team}', [PostController::class, 'save'])->middleware('auth')->name('postSave');
Route::get('/post/{id}', [PostController::class, 'index'])->name('post');
Route::get('/post/{id}/delete', [PostController::class, 'destroy'])->middleware('auth')->name('postDel');

// Комментарии
Route::post('/post/{post_id}/comm-new', [CommentController::class, 'create'])->middleware('auth')->name('commNew');
Route::get('/comm/{id}/delete', [CommentController::class, 'destroy'])->middleware('auth')->name('commDel');

// Теги
Route::post('/tag/new', [TagController::class, 'create'])->middleware('auth')->name('tagNew');
Route::get('/tag/{id}/delete', [TagController::class, 'destroy'])->middleware('auth')->name('tagDel');

// Админка
Route::get('/admin/tags', [AdminController::class, 'tagList'])->middleware('auth')->name('tagList');
Route::get('/admin/users', [AdminController::class, 'userList'])->middleware('auth')->name('userList');
Route::get('/admin/user/{id}', [AdminController::class, 'userEdit'])->middleware('auth')->name('userEdit');

// Подписка
Route::get('/subscribe/{type}/{id}', [SubscribesController::class, 'subs'])->middleware('auth')->name('subscribe');

// Просмотр файлов
Route::get('/storage/imgs/users/avatars/{filename}', function ($filename) {
    $path = storage_path('app/public/imgs/users/avatars/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
