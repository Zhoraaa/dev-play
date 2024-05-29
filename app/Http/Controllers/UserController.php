<?php

namespace App\Http\Controllers;

use App\Models\DevToTeamConnection;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscribes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Storage;
use Validator;

class UserController extends Controller
{
    public function index($login)
    {
        //
        $count = User::where('login', $login)->count();

        if ($count) {
            $user = User::where('login', $login)
                ->join('roles', 'users.role_id', 'roles.id')
                ->select(
                    'users.*',
                    'roles.name as role'
                )
                ->first();

            $user->created_at = Carbon::parse($user->created_at);

            // Преобразование времени в удобочитаемый формат
            $user->created_at = $user->created_at->diffForHumans() . ' <i class="text-secondary">(' . Carbon::parse($user->created_at)->format('d/m/Y H:i') . ')</i>';

            $subscribed = false;
            if (Auth::user()) {
                $subscribed = Subscribes::where('sub_type', '=', 'developer')
                    ->where('sub_for', '=', $user->id)
                    ->where('subscriber_id', '=', Auth::user()->id)
                    ->count() ? true : false;
            }

            // Список проектов созданных пользователем
            $projects = Project::leftJoin('users', 'users.id', '=', 'projects.author_id')
                ->leftJoin('dev_teams', 'dev_teams.id', '=', 'projects.team_rights_id')
                ->leftJoin('tag_to_project_connections', 'projects.id', '=', 'tag_to_project_connections.project_id')
                ->leftJoin('tags', 'tags.id', '=', 'tag_to_project_connections.tag_id')
                ->select(
                    'projects.*',
                    'users.avatar',
                    'users.role_id',
                    'users.login as author',
                    'dev_teams.url as author_team_url',
                    'dev_teams.name as author_team',
                    'dev_teams.avatar as author_team_avatar',
                    DB::raw('GROUP_CONCAT(DISTINCT tags.name ORDER BY tags.name SEPARATOR ", ") as tags')
                )
                ->where('projects.author_id', '=', $user->id)
                ->groupBy('projects.id')
                ->orderBy('created_at', 'asc')
                ->get();

            // Список постов, написанных пользователем недавно
            $posts = Post::orderBy('created_at', 'desc')
                ->where('type_id', '=', 1)
                ->leftJoin('users', 'users.id', 'posts.author_id')
                ->leftJoin('dev_teams', 'dev_teams.id', 'posts.author_mask')
                ->select(
                    'posts.*',
                    'users.login as author',
                    'users.avatar',
                    'users.role_id',
                    'dev_teams.name as showing_author',
                    'dev_teams.url as showing_author_url',
                    'dev_teams.avatar as showing_author_avatar',
                )
                ->where('author_id', '=', $user->id)
                ->get(3);

            // Список команд, в которых присутствует пользователь
            $teams = DevToTeamConnection::where('developer_id', '=', $user->id)
                ->join('dev_teams', 'dev_teams.id', 'dev_to_team_connections.team_id')
                ->select('dev_teams.*', 'dev_to_team_connections.role')
                ->get();

            foreach ($posts as $post) {
                // Форматирование даты и времени создания (created_at)
                $createdAt = Carbon::parse($post->created_at);
                $createdAtFormatted = $createdAt->format('d/m/Y H:i');
                $createdAtDiff = $createdAt->diffForHumans();

                // Форматирование даты и времени обновления (updated_at)
                $updatedAt = Carbon::parse($post->updated_at);
                $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
                $updatedAtDiff = $updatedAt->diffForHumans();

                // Формируем окончательные строки для отображения
                $post->formatted_created_at = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
                $post->formatted_updated_at = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";
            }

            // Если разраб, можно пригласить в свою команду.
            $teamsToInvite = false;
            if (Auth::user()->role_id === 2 && $user->role_id === 2) {
                $teamsToInvite = DevToTeamConnection::where('developer_id', '=', Auth::user()->id)
                    ->where('role', 'Глава')
                    ->join('dev_teams', 'dev_teams.id', 'dev_to_team_connections.team_id')
                    ->select(
                        'dev_teams.id',
                        'dev_teams.name'
                    )
                    ->get();
            }

            return view('user.page', [
                'user_exist' => true,
                'user' => $user,
                'subscribed' => $subscribed,
                'teams' => $teams,
                'posts' => $posts,
                'projects' => $projects,
                'teamsToInvite' => $teamsToInvite,
            ]);
        }

        return view('user.page', [
            'user_exist' => false,
        ]);
    }
    public function create(Request $userRaw)
    {
        //
        $validator = Validator::make($userRaw->all(), [
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

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // dd($userRaw->all());

        $user = User::create([
            'login' => $userRaw->login,
            'email' => $userRaw->email,
            'password' => $userRaw->password,
            'avatar' => null,
            'role_id' => 1,
            'banned' => 0,
            'created_at' => now()
        ]);

        Auth::login($user);

        return redirect()->route('user', ['login' => $user->login])->with('success', 'Приветствуем, ' . $user->login . '!');
    }
    public function login(Request $logindata)
    {
        //
        $validator = Validator::make($logindata->all(), [
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

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (Auth::attempt($logindata->only('login', 'password'))) {
            return redirect()->route('user', ['login' => $logindata->login])->with('success', 'Добро пожаловать, ' . $logindata->login . '!');
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
        $validator = Validator::make($newData->all(), [
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
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


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
            $user = User::where('login', $login)->first();
            $user->about = $this->removeLinks($user->about);
            $user->about = str_replace("<b>", "**", $user->about);
            $user->about = str_replace("</b>", "**", $user->about);
            $user->about = str_replace("<i>", "_", $user->about);
            $user->about = str_replace("</i>", "_", $user->about);
            $user->about = str_replace("<br>", "\r\n", $user->about);

            return view('user.editor', compact('user'))->with('warning', 'Вы заходите на опасную территорию.');
        }
        return redirect()->route('home')->with('error', 'Пользователь не найден.');
    }

    public function beDeveloper()
    {
        if (
            Auth::user()->email &&
            Auth::user()->about &&
            Auth::user()->avatar
        ) {
            if (Auth::user()->banned) {
                return redirect()->back()->with('error', 'Забаненный пользователь не может стать разработчиком!');
            } else {
                Auth::user()->update(['role_id' => 2]);

                return redirect()->back()->with('success', 'Теперь вы разработчик!');
            }
        } else {
            return redirect()->back()->with('error', 'Заполните все данные о пользователе, чтобы стать разработчиком!');
        }
    }

    // Метод для обновления аватарки
    public function avatarUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png,gif',
        ]);


        $image = $request->file('avatar');

        if ($request->hasFile('avatar')) {
            // Удаление предыдущей аватарки
            if (Storage::exists('public/imgs/users/avatars/' . Auth::user()->avatar)) {
                Storage::delete('public/imgs/users/avatars/' . Auth::user()->avatar);
            }

            // Генерация имени файла
            $fileName = time() . '_' . Auth::user()->login . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/imgs/users/avatars/', $fileName);

            // Обновление записи в базе
            User::where('id', Auth::user()->id)
                ->update([
                    'avatar' => $fileName
                ]);

            return redirect()->back()->with('success', 'Аватар обновлён.');
        }

        return redirect()->back()->with('error', 'Не удалось загрузить аватар.');
    }

    public function avatarDelete()
    {
        // Удаление аватарки
        if (Storage::exists('public/imgs/users/avatars/' . Auth::user()->avatar)) {
            Storage::delete('public/imgs/users/avatars/' . Auth::user()->avatar);

            // Очистка поля avatar в базе данных
            User::where('id', Auth::user()->id)
                ->update([
                    'avatar' => null
                ]);

            return redirect()->back()->with('success', 'Аватар удалён.');
        } else {
            return redirect()->back()->with('error', 'У вас нет аватарки!');
        }
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
}
