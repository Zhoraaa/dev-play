<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DevTeamController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return view('test');
});

// Конкретные страницы
Route::get('/', function () {
    return view('home');
})->name('home');
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
// Становление разработчиком
Route::get('/user/{login}/beDeveloper', [UserController::class, 'beDeveloper'])->middleware('auth')->name('beDeveloper');

// Команда разработчиков

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
Route::get('/project/{project}', [ProjectController::class, 'index'])->middleware('auth')->name('snapshotNew');
Route::get('/project/{project}/{id}/edit', [ProjectController::class, 'editor'])->middleware('auth')->name('snapshotEditor');
Route::get('/project/{project}/{id}/delete', [ProjectController::class, 'destroy'])->middleware('auth')->name('snapshotDelete');

// Посты

// Тикеты

// Теги
Route::post('/tag/new', [TagController::class, 'create'])->middleware('auth')->name('tagNew');
Route::get('/tag/{id}/delete', [TagController::class, 'destroy'])->middleware('auth')->name('tagDel');

// Админка
Route::get('/admin/users', [AdminController::class, 'userList'])->middleware('auth')->name('userList');
Route::get('/admin/tags', [AdminController::class, 'tagList'])->middleware('auth')->name('tagList');
