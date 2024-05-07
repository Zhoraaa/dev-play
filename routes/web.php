<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DevTeamController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
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

// Пользователь
Route::get('/auth', function () {
    return view('user.auth');
})->name('auth');
Route::get('/reg', function () {
    return view('user.reg');
})->name('reg');
Route::post('/signUp', [UserController::class, 'create'])->name('signUp');
Route::post('/signIn', [UserController::class, 'login'])->name('signIn');
Route::get('/signOut', [UserController::class, 'logout'])->name('signOut');
Route::get('/user/{login}', [UserController::class, 'index'])->name('userpage');
Route::post('/user/delete', [UserController::class, 'destroy'])->name('userdelete');

// Команда разработчиков
Route::get('/devteam', [DevTeamController::class, 'list'])->name('devTeams');

// Проекты

// Посты

// Тикеты

// Админка
Route::get('/admin/dev-tickets', [AdminController::class, 'dev-tickets'])->name('wantToBeDeveloper');
Route::get('/admin/users', [AdminController::class, 'user-list'])->name('userList');
Route::get('/admin/tags', [AdminController::class, 'tag-list'])->name('tagList');
