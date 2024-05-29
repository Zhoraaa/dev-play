<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use App\Models\Snapshots;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjectMedia;
use App\Models\Project; 
use App\Models\Subscribes;
use Illuminate\Support\Facades\Mail;

class SnapshotsController extends Controller
{
    //
    public function index($url, $build)
    {
        $snapshot = Snapshots::where('snapshots.name', '=', $build)
            ->join('projects', 'projects.id', 'snapshots.project_id')
            ->select(
                'snapshots.*',
                'projects.author_id',
                'projects.team_rights_id',
                'projects.name as project_name',
                'projects.url as project_url'
            )
            ->first();

        // Форматирование даты и времени создания (created_at)
        $createdAt = Carbon::parse($snapshot->created_at);
        $createdAtFormatted = $createdAt->format('d/m/Y H:i');
        $createdAtDiff = $createdAt->diffForHumans();

        // Форматирование даты и времени обновления (updated_at)
        $updatedAt = Carbon::parse($snapshot->updated_at);
        $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
        $updatedAtDiff = $updatedAt->diffForHumans();

        // Формируем окончательные строки для отображения
        $snapshot->formatted_created_at = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
        $snapshot->formatted_updated_at = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";

        if (!$snapshot) {
            return redirect()->back()->with('error', 'Версия не найдена');
        }

        $medias = ProjectMedia::where('project_id', '=', $snapshot->project_id)
            ->where('snapshot_id', '=', $snapshot->id)
            ->where('for_download', '=', 0)
            ->get()
            ->toArray();

        $downloadable = ProjectMedia::where('project_id', '=', $snapshot->project_id)
            ->where('snapshot_id', '=', $snapshot->id)
            ->where('for_download', '=', 1)
            ->get();



        $canedit = $snapshot->author_id == Auth::user()->id ? true : false;

        return view('snapshot.page', [
            'url' => $url,
            'snapshot' => $snapshot,
            'medias' => $medias,
            'downloadable' => $downloadable,
            'canedit' => $canedit
        ]);
    }
    public function save(Request $request, $url)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:snapshots|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/u', // Обязательное поле, максимум 255 символов
            'description' => 'required', // Обязательное поле для описания
            'images.*' => 'nullable|file|mimes:jpeg,png,gif,mp4,avi',
            'downloadable.*' => 'nullable|file|mimes:pdf,doc,docx,zip',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.max' => 'Поле "Название" должно содержать не более 255 символов.',
            'name.regex' => 'Для названия версии используйте только латиницу или спецсимолы.',
            'name.unique' => 'Название версии должно быть уникальным.',

            'description.required' => 'Поле "Описание" обязательно для заполнения.',

            'images.*.file' => 'Поле :attribute должно быть файлом.',
            'images.*.mimes' => 'Поле :attribute должно быть файлом одного из типов: jpeg, png, gif, mp4, avi.',
            'downloadable.*.file' => 'Поле :attribute должно быть файлом.',
            'downloadable.*.mimes' => 'Поле :attribute должно быть файлом одного из типов: pdf, doc, docx, zip.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (isset($request->id)) {
            $build = $this->update($request, $url);
            $response = 'Версия успешно загружена!';
        } else {
            $build = $this->create($request, $url);
            $response = 'Данные версии обновлены.';
        }

        if ($build === false) {
            return redirect()->back()->with('error', 'Ошибка, проeкт не найден')->withInput();
        }

        return redirect()->route('snapshot', [
            'url' => $url,
            'build' => $build,
        ])->with('success', $response);
    }
    private function create($data, $url)
    {
        $project = Project::where('url', $url)->first();

        if (!$project) {
            return false; // Возвращаем false, если проект не найден
        }
        // Преобразовываем текст описания по ключевым символам
        $data['description'] = strip_tags($data['description']);
        $data['description'] = $this->handle($data['description'], '**');
        $data['description'] = $this->handle($data['description'], '_');
        $data['description'] = str_replace("\r\n", "<br>", $data['description']);
        $data['description'] = $this->handleLinks($data['description']);

        // Создаем снапшот
        $snapshot = Snapshots::create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        // Загрузка файлов
        $this->multiloadMedia($data, $project->id, 'snapshots/media/', 'images', 0, $snapshot->id);
        $this->multiloadMedia($data, $project->id, 'snapshots/downloadable/', 'downloadable', 1, $snapshot->id);

        // Рассылка подписчикам проекта

        // Рассылка подписчикам разработчика
        $subs = Subscribes::where('sub_for', '=', $snapshot->project_id)
            ->where('sub_type', '=', 'project')
            ->join('users', 'users.id', 'subscribes.subscriber_id')
            ->select(
                'users.email'
            )
            ->get();

        if (!empty($subs->all())) {
            foreach ($subs as $sub) {
                Mail::to($sub->email)->send(
                    new CustomEmail(
                        'Новая версия проекта "' . $project->name . '".',
                        'Подробнее можете ознакмиться по <a href="' . route('snapshot', ['url' => $project->url, 'build'=> $snapshot->name]) . '">ссылке</a>.'
                    )
                );
            }
        }

        Project::where('url', $url)->update(['updated_at' => now()]);

        return $snapshot->name;
    }
    private function update(Request $newData, $url)
    {
        // Получаем старые данные версии
        $oldSnapshot = Snapshots::where('snapshots.id', $newData->id)
            ->join('projects', 'projects.id', 'snapshots.project_id')
            ->select('snapshots.*', 'projects.author_id', 'projects.id as proj_id')
            ->first();

        // Проверяем доступ к редактированию версии
        if (Auth::user()->id == $oldSnapshot->author_id) {
            // Преобразуем текст описания по ключевым символам
            $newData['description'] = strip_tags($newData['description']);
            $newData['description'] = $this->handle($newData['description'], '**');
            $newData['description'] = $this->handle($newData['description'], '_');
            $newData['description'] = str_replace("\r\n", "<br>", $newData['description']);
            $newData['description'] = $this->handleLinks($newData['description']);

            unset($newData['_token']);

            // Формируем массив изменений
            $differences = [];
            foreach ($newData->all() as $key => $value) {
                if ($newData[$key] != $oldSnapshot[$key] && $newData->has($key)) {
                    $differences[$key] = $value;
                }
            }
            // Обновляем данные версии
            $oldSnapshot->update($differences);

            Project::where('url', $url)->update(['updated_at' => now()]);

            return $newData->name;
        } else {
            return redirect()->back()->with('error', 'Отказано в доступе');
        }
    }
    public function editor($url, $build)
    {
        $snapshot = Snapshots::where('name', $build)->first();
        $snapshot->description = $this->removeLinks($snapshot->description);
        $snapshot->description = str_replace("<b>", "**", $snapshot->description);
        $snapshot->description = str_replace("</b>", "**", $snapshot->description);
        $snapshot->description = str_replace("<i>", "_", $snapshot->description);
        $snapshot->description = str_replace("</i>", "_", $snapshot->description);
        $snapshot->description = str_replace("<br>", "\r\n", $snapshot->description);

        return view('snapshot.editor', [
            'url' => $url,
            'snapshot' => $snapshot
        ]);
    }
    public function destroy(Request $request, $url, $build)
    {
        $snapshot = Snapshots::join('projects', 'projects.id', 'snapshots.project_id')
            ->join('users', 'projects.author_id', '=', 'users.id')
            ->select('users.password')
            ->where('snapshots.name', $build)
            ->first();

        if (Hash::check($request->password, $snapshot->password)) {
            Snapshots::where('name', $build)->delete();

            return redirect()->route('project', ['url' => $url])->with('success', 'Снапшот удалён');
        } else {
            return redirect()->route('snapshot', ['build' => $build])->with('error', 'Неверный пароль');
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


    // Обработка изображений
    private function multiloadMedia($data, $proj_id, $path, $field, $downloadable, $snapshot)
    {
        if ($data->hasFile($field)) {
            $files = $data->file($field);
            $fileNames = [];

            $counter = 1;

            foreach ($files as $file) {
                // Генерация имени файла
                $fileName = 'proj_' . $proj_id . '_file_' . $counter . '_' . $file->getClientOriginalName();
                $mediaPath = $file->storeAs('public/' . $path . $fileName);

                // Сохранение пути файла для дальнейшего использования
                $fileNames[] = $fileName;

                // Сохранение в базе данных
                ProjectMedia::create([
                    'project_id' => $proj_id,
                    'author_id' => Auth::user()->id,
                    'file_name' => $fileName,
                    'snapshot_id' => $snapshot,
                    'for_download' => $downloadable,
                    'created_at' => now()
                ]);

                $counter++;
            }
        }
    }
}
