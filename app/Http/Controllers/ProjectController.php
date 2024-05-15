<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TagToProjectConnection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    //

    public function index($url)
    {
        $projectdata = Project::where('url', $url)->first();

        if ($projectdata) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($projectdata->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($projectdata->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $projectdata->created_at_formatted = "$createdAtDiff (<i class='text-secondary'>$createdAtFormatted</i>)";
            $projectdata->updated_at_formatted = "$updatedAtDiff (<i class='text-secondary'>$updatedAtFormatted</i>)";
        }

        return view('project.page', [
            'project_exist' => Project::where('url', $url)->exists(),
            'projectdata' => $projectdata,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255', // Обязательное поле, максимум 255 символов
            'url' => [
                'required',
                'alpha_dash', // Только буквы, цифры, дефисы и подчеркивания
                'max:255',
                Rule::unique('projects')->ignore($request->id), // Уникальное значение в таблице projects, игнорируя текущую запись
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

        if ($request->id) {
            $this->update($request);
            return redirect()->route('project', ['url' => $request->url])->with('success', 'Данные сохранены');
        } else {
            $this->create($request);
            return redirect()->route('project', ['url' => $request->url])->with('success', 'Проект успешно создан');
        }
    }

    public function editor($url)
    {
        $count = Project::where('url', $url)->count();
        if ($count) {
            $projectdata = Project::where('url', $url)->first();
            $projectdata->description = $this->removeLinks($projectdata->description);
            $projectdata->description = str_replace("<b>", "**", $projectdata->description);
            $projectdata->description = str_replace("</b>", "**", $projectdata->description);
            $projectdata->description = str_replace("<i>", "_", $projectdata->description);
            $projectdata->description = str_replace("</i>", "_", $projectdata->description);
            $projectdata->description = str_replace("<br>", "\r\n", $projectdata->description);

            $tags = Tag::orderBy('name', 'asc')->get();

            return view('project.editor', ['projectdata' => $projectdata, 'tags' => $tags])->with('warning', 'Вы заходите на опасную территорию.');
        }
        return redirect()->route('home')->with('error', 'Проект не найден.');
    }

    private function create($data)
    {
        $data->description = strip_tags($data->description);
        $data->description = $this->handle($data->description, '**');
        $data->description = $this->handle($data->description, '_');
        $data->description = str_replace("\r\n", "<br>", $data->description);
        $data->description = $this->handleLinks($data->description);

        Project::create([
            'name' => $data->name,
            'description' => $data->description,
            'cover' => $data->cover,
            'url' => $data->url,
            'author_id' => Auth::user()->id,
            'team_rights_id' => $data->team,
            'updated_at' => now()
        ]);
    }
    public function update(Request $newData)
    {
        // Записываем важные данные в отдельный массив. 
        $newDataText = $newData->all();
        unset($newDataText['_token']);
        $newDataText = array_filter($newDataText, function ($key) {
            return strpos($key, "tag-") !== 0;
        }, ARRAY_FILTER_USE_KEY);
        // Отделяем проставленные теги от остальных данных.
        $tagsRaw = array_filter($newData->all(), function ($key) {
            return strpos($key, "tag-") === 0;
        }, ARRAY_FILTER_USE_KEY);
        $tags = array();
        foreach ($tagsRaw as $key => $val) {
            $tagID = str_replace('tag-', '', $key);
            $tags += [$tagID => $tagID];
        }
        // Теперь у нас есть массив текстовых данных и массив отмеченных тегов

        // Достаём старые данные
        $oldData = Project::where('id', $newData->id)->first();

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
            $oldTags = TagToProjectConnection::where('project_id', '=', $newData->id)
                ->select('tag_to_project_connections.tag_id')
                ->get();
            $oldTags = $oldTags->all();
            dd($oldTags[0]->all());

            dd('debug');
            return redirect()->back()->with('success', 'Данные сохранены');
        } else {
            return redirect()->back()->with('error', 'Отказано в доступе');
        }
    }

    public function destroy(Request $request, $url)
    {
        $project = Project::join('users', 'projects.author_id', '=', 'users.id')
            ->select('projects.*', 'users.password')
            ->where('url', $url)->first();

        if (Hash::check($request->password, $project->password)) {
            Project::where('url', $url)->delete();

            return redirect()->route('userpage', ['login' => Auth::user()->login])->with('success', 'Спасибо, что размещали свой проект у нас!');
        } else {
            return redirect()->route('project', ['url' => $project->url])->with('error', 'Неверный пароль');
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
