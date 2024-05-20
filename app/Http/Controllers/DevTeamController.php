<?php

namespace App\Http\Controllers;

use App\Models\DevTeam;
use App\Models\DevToTeamConnection;
use App\Models\Subscribes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DevTeamController extends Controller
{
    public function index($url)
    {
        $team = DevTeam::where('url', $url)->first();

        if ($team) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($team->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($team->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $team->created_at_formatted = "$createdAtDiff (<i class='text-secondary'>$createdAtFormatted</i>)";
            $team->updated_at_formatted = "$updatedAtDiff (<i class='text-secondary'>$updatedAtFormatted</i>)";
        } else {
            return redirect()->back()->with('error', 'Команда не найдена');
        }

        $ismember = DevToTeamConnection::where('team_id', '=', $team->id)
            ->where('developer_id', '=', Auth::user()->id)
            ->count()
            ? true : false;


        if (!$ismember) {
            $canedit = 0;
        } else {
            switch (
                DevToTeamConnection::where('team_id', '=', $team->id)
                    ->where('developer_id', '=', Auth::user()->id)
                    ->first()
                    ->role
            ) {
                default:
                    $canedit = 0;
                    break;
                case 'Разработчик':
                    $canedit = 1;
                    break;
                case 'Глава':
                    $canedit = 2;
                    break;
            }
        }

        $members = DevToTeamConnection::where('team_id', '=', $team->id)
            ->join('users', 'users.id', 'dev_to_team_connections.developer_id')
            ->select('users.*', 'dev_to_team_connections.role')
            ->get();


        $subscribed = false;
        if (Auth::user()) {
            $subscribed = Subscribes::where('sub_type', '=', 'dev_team')
                ->where('sub_for', '=', $team->id)
                ->where('subscriber_id', '=', Auth::user()->id)
                ->count() ? true : false;

        }

        return view('devteam.page', [
            'url' => $url,
            'team' => $team,
            'canedit' => $canedit,
            'members' => $members,
            'subscribed' => $subscribed
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255', // Обязательное поле, максимум 255 символов
            'url' => [
                'required',
                'alpha_dash', // Только буквы, цифры, дефисы и подчеркивания
                'max:255',
                Rule::unique('dev_teams')->ignore($request->id), // Уникальное значение в таблице devteams, игнорируя текущую запись
            ],
            'description' => 'required', // Обязательное поле для описания
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Разрешенные типы файлов и максимальный размер
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.max' => 'Поле "Название" должно содержать не более 255 символов.',
            'url.required' => 'Поле "URL" обязательно для заполнения.',
            'url.alpha_dash' => 'Поле "URL" может содержать только буквы, цифры, дефисы и подчеркивания.',
            'url.max' => 'Поле "URL" должно содержать не более 255 символов.',
            'url.unique' => 'Указанный URL уже используется.',
            'description.required' => 'Поле "Описание" обязательно для заполнения.',
            'cover.image' => 'Файл должен быть изображением.',
            'cover.mimes' => 'Поддерживаемые форматы изображений: jpeg, png, jpg, gif.',
            'cover.max' => 'Максимальный размер изображения: 2048 КБ.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->id) {
            $this->update($request);
            $string = 'Данные сохранены.';
        } else {
            $this->create($request);
            $string = 'Команда успешно создана!';
        }
        return redirect()->route('devteam', ['url' => $request->url])->with('success', $string);
    }

    public function editor($url)
    {
        $count = DevTeam::where('url', $url)->count();
        if ($count) {
            $team = DevTeam::where('url', $url)->first();
            $team->description = $this->removeLinks($team->description);
            $team->description = str_replace("<b>", "**", $team->description);
            $team->description = str_replace("</b>", "**", $team->description);
            $team->description = str_replace("<i>", "_", $team->description);
            $team->description = str_replace("</i>", "_", $team->description);
            $team->description = str_replace("<br>", "\r\n", $team->description);

            $users = User::orderBy('login', 'asc')
                ->where('id', '!=', Auth::user()->id)
                ->get();

            $membersIds = DevToTeamConnection::join('dev_teams', 'dev_teams.id', 'dev_to_team_connections.team_id')
                ->where('dev_teams.url', $team->url)
                ->pluck('developer_id')
                ->toArray();

            $members = [];
            foreach ($users as $user) {
                $selectedTags[$user->id] = in_array($user->id, $membersIds) ? 'checked' : null;
            }

            return view('devteam.editor', [
                'team' => $team,
                'devs' => $users,
                'members' => $members,
            ])->with('warning', 'Вы заходите на опасную территорию.');
        }
        return redirect()->route('home')->with('error', 'Команда не найдена.');
    }

    private function create($data)
    {
        $data->description = strip_tags($data->description);
        $data->description = $this->handle($data->description, '**');
        $data->description = $this->handle($data->description, '_');
        $data->description = str_replace("\r\n", "<br>", $data->description);
        $data->description = $this->handleLinks($data->description);

        $team = DevTeam::create([
            'name' => $data->name,
            'avatar' => null,
            'description' => $data->description,
            'url' => $data->url,
            'created_at' => now(),
        ]);

        $membersRaw = array_filter($data->all(), function ($key) {
            return strpos($key, "dev-") === 0;
        }, ARRAY_FILTER_USE_KEY);
        $members = array();
        foreach ($membersRaw as $key => $val) {
            $memberID = str_replace('tag-', '', $key);
            $members += [$memberID => $memberID];
        }

        DevToTeamConnection::create([
            'developer_id' => Auth::user()->id,
            'team_id' => $team->id,
            'role' => 'Глава'
        ]);


        foreach ($members as $member_id) {
            DevToTeamConnection::create([
                'developer_id' => $member_id,
                'team_id' => $team->id,
                'role' => 'Приглашён'
            ]);
        }
    }
    private function update(Request $newData)
    {
        // Записываем важные данные в отдельный массив. 
        $newDataText = $newData->all();
        unset($newDataText['_token']);
        $newDataText = array_filter($newDataText, function ($key) {
            return strpos($key, "dev-") !== 0;
        }, ARRAY_FILTER_USE_KEY);

        // Отделяем выделенных разработчиков от остальных данных.
        $membersRaw = array_filter($newData->all(), function ($key) {
            return strpos($key, "dev-") === 0;
        }, ARRAY_FILTER_USE_KEY);
        $members = array();
        foreach ($membersRaw as $key => $val) {
            $memberID = str_replace('dev-', '', $key);
            $members += [$memberID => $memberID];
        }
        // Теперь у нас есть массив текстовых данных и массив отмеченных разработчиков 

        // Достаём старые данные
        $oldData = DevTeam::where('id', $newData->id)->first();

        // В случае успешной проверки безопасности работаем дальше
        if (Auth::user()->id == $oldData->author_id) {
            // Преобразовываем текст описания по ключевым символам
            $newDataText['description'] = strip_tags($newDataText['description']);
            $newDataText['description'] = $this->handle($newDataText['description'], '**');
            $newDataText['description'] = $this->handle($newDataText['description'], '_');
            $newDataText['description'] = str_replace("\r\n", "<br>", $newDataText['description']);
            $newDataText['description'] = $this->handleLinks($newDataText['description']);
            // Заготавливаем массив
            $differences = array();

            foreach ($newDataText as $key => $value) {
                if ($newDataText[$key] != $oldData[$key] && $newDataText) {
                    // Записываем в этот массив пришедшие данные, отличающиеся от таковых в базе
                    $differences += [$key => $value];
                }
            }

            // Обновляем запись в базе.
            $oldData->update($differences);

            // Достаём записи о тегах, причисленных к проекту
            // И записываем их в отдельный массив данных
            $oldMembers = DevToTeamConnection::where('team_id', '=', $newData->id)
                ->select('dev_to_team_connections.developer_id')
                ->get();
            $oldMembers = $oldMembers->all();
            $oldMembersArray = array();
            foreach ($oldMembers as $oldTag) {
                $oldMembersArray += [$oldTag->tag_id => $oldTag->tag_id];
            }
            // Записываем ключи (равные id тегов) для сравнения наличия тегов в массивах
            $keysMembers = array_keys($members);
            $keysOTA = array_keys($oldMembersArray);
            // Если массив пришёл в этот раз из формы, но его не было в базе, он помечается для добавления, если наоборот - для удаления.
            $toAdd = array_diff($keysMembers, $keysOTA); // Массив на добавление
            $toDel = array_diff($keysOTA, $keysMembers); // Массив на удаление

            // Добавляем и удаляем записи в базе в соответствии с массивами.
            foreach ($toAdd as $tag) {
                DevToTeamConnection::create([
                    'team_id' => $newData->id,
                    'developer_id' => $tag,
                    'role' => 'Приглашён'
                ]);
            }
            foreach ($toDel as $tag) {
                DevToTeamConnection::where([
                    'team_id' => $newData->id,
                    'developer_id' => $tag
                ])->delete();
            }
            return redirect()->back()->with('success', 'Данные сохранены');
        } else {
            return redirect()->back()->with('error', 'Отказано в доступе');
        }
    }

    public function destroy(Request $request, $url)
    {
        $team = DevTeam::join('users', 'teams.author_id', '=', 'users.id')
            ->select('teams.*', 'users.password')
            ->where('url', $url)->first();

        if (Hash::check($request->password, $team->password)) {
            DevToTeamConnection::where('team_id', '=', $team->id)->delete();
            DevTeam::where('url', $url)->delete();

            return redirect()->route('userpage', ['login' => Auth::user()->login])->with('success', 'Спасибо, что были в команде с нами!');
        } else {
            return redirect()->route('devteam', ['url' => $team->url])->with('error', 'Неверный пароль');
        }
    }

    // Метод для обновления аватарки
    public function avatarUpdate(Request $request, $url)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png,gif',
        ]);

        $image = $request->file('avatar');

        $oldAvatar = DevTeam::where('url', $url)->first()->avatar;

        if ($request->hasFile('avatar')) {
            // Удаление предыдущей аватарки
            if (Storage::exists('public/imgs/teams/avatars/' . $oldAvatar)) {
                Storage::delete('public/imgs/teams/avatars/' . $oldAvatar);
            }

            // Генерация имени файла
            $fileName = time() . '_' . $url . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/imgs/teams/avatars/', $fileName);

            // Обновление записи в базе
            User::where('id', Auth::user()->id)
                ->update([
                    'avatar' => $fileName
                ]);

            return redirect()->back()->with('success', 'Аватар обновлён.');
        }

        return redirect()->back()->with('error', 'Не удалось загрузить аватар.');
    }

    public function avatarDelete($url)
    {
        // Берём данные о старой аватарке
        $oldAvatar = DevTeam::where('url', $url)->first()->avatar;
        // Удаление аватарки
        if (Storage::exists('public/imgs/teams/avatars/' . Auth::user()->avatar)) {
            Storage::delete('public/imgs/teams/avatars/' . Auth::user()->avatar);

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

        // dd($formattedText);

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
