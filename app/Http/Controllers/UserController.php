<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
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
            $userdata = User::where('login', $login)
                ->join('roles', 'users.role_id', 'roles.id')
                ->select('users.*', 'roles.name as role')
                ->first();

            $userdata->created_at = Carbon::parse($userdata->created_at);

            // Преобразование времени в удобочитаемый формат
            $userdata->created_at = $userdata->created_at->diffForHumans() . ' <i class="text-secondary">(' . Carbon::parse($userdata->created_at)->format('d/m/Y H:i') . ')</i>';

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
            'login.unique' => 'Этот логин уже используется',
            'password.required' => 'Впишите пароль!',
            'password.min' => 'Пароль должен быть длиннее 6 символов.',
            'password.max' => 'Пароль должен быть короче 32 символов.',
            'email.required' => 'Впишите почту!',
            'email.email' => 'Впишите валидный почтовый ящик! Пример: example@mail.ru',
            'email.unique' => 'Эта почта уже используется'
        ]);

        // dd($userRaw->all());

        $user = User::create([
            'login' => $userRaw->login,
            'email' => $userRaw->email,
            'password' => $userRaw->password,
            'avatar' => $userRaw->avatar,
            'role_id' => 1,
            'banned' => 0
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
    public function update(Request $newData)
    {
        $newData->validate([
            'login' => 'required|min:6|max:32',
            'email' => 'nullable|email',
            'password' => 'required|min:6|max:32',
        ], [
            'login.required' => 'Впишите логин!',
            'login.min' => 'Логин должен быть длиннее 6 символов.',
            'login.max' => 'Логин должен быть короче 32 символов.',
            'login.unique' => 'Этот логин уже используется',
            'password.required' => 'Для подтверждения изменений необходим пароль.',
            'email.email' => 'Впишите валидный почтовый ящик! Пример: example@mail.ru',
            'email.unique' => 'Эта почта уже используется'
        ]);

        $newDataText = $newData->all();
        unset($newDataText['_token']);
        unset($newDataText['password']);
        // dd($newDataText);

        $oldData = User::where('id', Auth::user()->id)->first();

        if (Hash::check($newData->password, $oldData->password)) {
            $about = strip_tags($newData->about);
            $about = $this->handle($about, '**');
            $about = $this->handle($about, '_');
            $about = str_replace("\r\n", "<br>", $about);
            $about = $this->handleLinks($about);

            $differences = array();

            foreach ($newDataText as $key => $value) {
                if ($newDataText[$key] != $oldData[$key] && $newDataText && $key != 'about') {
                    $differences += [$key => $value];
                }
            }

            if ($oldData->about != $about) {
                $differences += ['about' => $about];
            }

            // dd($differences);

            $oldData->update($differences);
            return redirect()->back()->with('success', 'Данные сохранены');
        } else {
            return redirect()->back()->with('error', 'Неверный пароль');
        }
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

    public function editor($login)
    {
        $count = User::where('login', $login)->count();
        if ($count) {
            $userdata = User::where('login', $login)->first();
            $userdata->about = $this->removeLinks($userdata->about);
            $userdata->about = str_replace("<b>", "**", $userdata->about);
            $userdata->about = str_replace("</b>", "**", $userdata->about);
            $userdata->about = str_replace("<i>", "_", $userdata->about);
            $userdata->about = str_replace("</i>", "_", $userdata->about);
            $userdata->about = str_replace("<br>", "\r\n", $userdata->about);

            return view('user.editor', compact('userdata'))->with('warning', 'Вы заходите на опасную территорию.');
        }
        return redirect()->route('home')->with('error', 'Пользователь не найден.');
    }

    // Обработчики текста
    private function handle($rawText, $style_code)
    {
        $associate = [
            '**' => 'b',
            '_' => 'i',
        ];

        $openingTag = "<{$associate[$style_code]}>";
        $closingTag = "</{$associate[$style_code]}>";

        // Регулярное выражение для поиска стилизующих элементов
        $pattern = '/' . preg_quote($style_code) . '(.*?)' . preg_quote($style_code) . '/s';

        // Функция замены найденных стилей на теги
        $formattedText = preg_replace_callback(
            $pattern,
            function ($matches) use ($openingTag, $closingTag) {
                return $openingTag . $matches[1] . $closingTag;
            },
            $rawText
        );

        return $formattedText;
    }

    private function handleLinks($rawText)
    {
        // Регулярное выражение для поиска и замены ссылок в формате [текст](ссылка)
        $pattern = '/\[([^\]]+)\]\(([^)]+)\)/';

        // Функция замены найденных ссылок на HTML теги <a>
        $formattedText = preg_replace_callback($pattern, function ($matches) {
            $linkText = $matches[1];
            $url = strip_tags($matches[2]);

            return "<a href='{$url}'>{$linkText}</a>";
        }, $rawText);

        return $formattedText;
    }

    private function removeLinks($text)
    {
        // Используем регулярное выражение для замены тегов <a> на нужный формат
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=[\'"]([^\'"]+)[\'"][^>]*?>(.*?)<\/a>/i';
        $replacement = '[$2]($1)';

        // Заменяем найденные теги
        $processedText = preg_replace($pattern, $replacement, $text);

        // Возвращаем обработанный текст
        return $processedText;
    }


    public function beDeveloper()
    {
        // dd(Auth::user());
        Auth::user()->update(['role_id' => 2]);

        return redirect()->back()->with('success', 'Теперь вы разработчик!');
        // return redirect()->back()->with('error', 'Ошибка, не заполнены необходимые данные');
    }
}
