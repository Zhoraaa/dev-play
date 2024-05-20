<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subscribes;
use App\Models\Tag;
use App\Models\Snapshots;
use App\Models\TagToProjectConnection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Validator;

class ProjectController extends Controller
{
    //

    public function index($url)
    {
        $project = Project::where('url', $url)->first();

        if ($project) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($project->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($project->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $project->created_at_formatted = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
            $project->updated_at_formatted = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";

            // Описываем теги названиями
            $tags = TagToProjectConnection::where('project_id', '=', $project->id)
                ->join('tags', 'tags.id', 'tag_to_project_connections.tag_id')
                ->select('tags.*')
                ->get();

            $snapshots = Snapshots::where('project_id', '=', $project->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            return redirect()->back()->with('error', 'Проект не найден');
        }

        $subscribed = false;
        if (Auth::user()) {
            $subscribed = Subscribes::where('sub_type', '=', 'project')
                ->where('sub_for', '=', $project->id)
                ->where('subscriber_id', '=', Auth::user()->id)
                ->count() ? true : false;
            // dd($subscribed);
        }


        return view('project.page', [
            'url' => $url,
            'project' => $project,
            'tags' => $tags,
            'snapshots' => $snapshots,
            'subscribed' => $subscribed,
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
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


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
            $project = Project::where('url', $url)->first();
            $project->description = $this->removeLinks($project->description);
            $project->description = str_replace("<b>", "**", $project->description);
            $project->description = str_replace("</b>", "**", $project->description);
            $project->description = str_replace("<i>", "_", $project->description);
            $project->description = str_replace("</i>", "_", $project->description);
            $project->description = str_replace("<br>", "\r\n", $project->description);

            $tags = Tag::orderBy('name', 'asc')->get();

            $selectedTagIds = TagToProjectConnection::where('project_id', $project->id)
                ->pluck('tag_id')
                ->toArray();

            $selectedTags = [];
            foreach ($tags as $tag) {
                $selectedTags[$tag->id] = in_array($tag->id, $selectedTagIds) ? 'checked' : null;
            }

            return view('project.editor', [
                'project' => $project,
                'tags' => $tags,
                'selectedTags' => $selectedTags,
            ])->with('warning', 'Вы заходите на опасную территорию.');
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

        $proj = Project::create([
            'name' => $data->name,
            'description' => $data->description,
            'cover' => $data->cover,
            'url' => $data->url,
            'author_id' => Auth::user()->id,
            'team_rights_id' => $data->team,
            'updated_at' => now()
        ]);

        $tagsRaw = array_filter($data->all(), function ($key) {
            return strpos($key, "tag-") === 0;
        }, ARRAY_FILTER_USE_KEY);
        $tags = array();
        foreach ($tagsRaw as $key => $val) {
            $tagID = str_replace('tag-', '', $key);
            $tags += [$tagID => $tagID];
        }

        foreach ($tags as $tag_id) {
            TagToProjectConnection::create([
                'project_id' => $proj->id,
                'tag_id' => $tag_id
            ]);
        }
    }
    private function update(Request $newData)
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
            // И записываем их в отдельный массив данных
            $oldTags = TagToProjectConnection::where('project_id', '=', $newData->id)
                ->select('tag_to_project_connections.tag_id')
                ->get();
            $oldTags = $oldTags->all();
            $oldTagsArray = array();
            foreach ($oldTags as $oldTag) {
                $oldTagsArray += [$oldTag->tag_id => $oldTag->tag_id];
            }
            // Записываем ключи (равные id тегов) для сравнения наличия тегов в массивах
            $keysTags = array_keys($tags);
            $keysOTA = array_keys($oldTagsArray);
            // Если массив пришёл в этот раз из формы, но его не было в базе, он помечается для добавления, если наоборот - для удаления.
            $toAdd = array_diff($keysTags, $keysOTA); // Массив на добавление
            $toDel = array_diff($keysOTA, $keysTags); // Массив на удаление

            // Добавляем и удаляем записи в базе в соответствии с массивами.
            foreach ($toAdd as $tag) {
                TagToProjectConnection::create([
                    'project_id' => $newData->id,
                    'tag_id' => $tag
                ]);
            }
            foreach ($toDel as $tag) {
                TagToProjectConnection::where([
                    'project_id' => $newData->id,
                    'tag_id' => $tag
                ])->delete();
            }
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
            TagToProjectConnection::where('project_id', '=', $project->id)->delete();
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
