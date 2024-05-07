<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index($login)
    {
        //
        $count = User::where('login', $login)->count();

        if ($count) {
            $userdata = User::where('login', $login)->first();

            return view('user.page', ['user_exist' => true, 'userdata' => $userdata]);
        }

        return view('user.page', ['user_exist' => false]);
    }
    public function create(Request $userRaw)
    {
        //
        $userRaw->validate([
            'login' => 'required|min:6|max:32|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:32|confirmed',
        ], [
            'login.required' => 'Впишите логин!',
            'login.min' => 'Логин должен быть длиннее 6 символов.',
            'login.max' => 'Логин должен быть короче 32 символов.',
            'login.unique' => 'Логин занят',
            'password.required' => 'Впишите пароль!',
            'password.min' => 'Пароль должен быть длиннее 6 символов.',
            'password.max' => 'Пароль должен быть короче 32 символов.',
            'email.required' => 'Впишите почту!',
            'email.email' => 'Впишите валидный почтовый ящик! Пример: example@mail.ru'
        ]);

        // dd($userRaw->all());

        $user = User::create([
            'login' => $userRaw->login,
            'email' => $userRaw->email,
            'password' => $userRaw->password,
            'avatar' => $userRaw->avatar,
            'role_id' => 1,
            'banned' => 0,
            'created_at' => now()
        ]);

        Auth::login($user);

        return redirect()->route('userpage', ['login' => $user->login])->with('success', 'Приветствуем, ' . $user->login . '!');
    }
    public function login(Request $logindata)
    {
        //
        $logindata->validate([
            'login' => 'required|min:6|max:32',
            'password' => 'required|min:6|max:32',
        ], [
            'login.required' => 'Впишите логин!',
            'login.min' => 'Логин должен быть длиннее 6 символов.',
            'login.max' => 'Логин должен быть короче 32 символов.',
            'password.required' => 'Впишите пароль!',
            'password.min' => 'Пароль должен быть длиннее 6 символов.',
            'password.max' => 'Пароль должен быть короче 32 символов.',
        ]);

        if (Auth::attempt($logindata->only('login', 'password'))) {
            return redirect()->route('userpage', ['login' => $logindata->login])->with('success', 'Добро пожаловать, ' . $logindata->login . '!');
        }

        return redirect()->back()->with('error', 'Ошибка авторизации.');
    }
    public function logout()
    {
        //
        Auth::logout();

        return redirect()->route('home')->with('success', 'До новых встреч!');
    }
    public function update(Request $newdata)
    {
        //
        $oldData = User::where(Auth::user()->id)->first();

        return view('home')->with('success', '');
    }
    public function destroy(Request $request)
    {
        $user = User::where('login', Auth::user()->login)->first();

        if (Hash::check($request->password, $user->password)) {
            $user->delete();

            return redirect()->route('home')->with('success', 'Страница удалена. Спасибо что были с нами!');
        }

        return redirect()->back()->with('error', 'Страница не удалена.');
    }
}
