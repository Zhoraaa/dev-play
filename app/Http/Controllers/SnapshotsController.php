<?php

namespace App\Http\Controllers;

use App\Models\Snapshots;
use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjectMedia;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
        $snapshot->created_at_formatted = "$createdAtDiff (<i class='text-secondary'>$createdAtFormatted</i>)";
        $snapshot->updated_at_formatted = "$updatedAtDiff (<i class='text-secondary'>$updatedAtFormatted</i>)";

        if (!$snapshot) {
            return redirect()->back()->with('error', 'Версия не найдена');
        }

        // На всякий случай создадим предупреждение, если версия редактировалась.
        $warning = false;
        if ($createdAtFormatted !== $updatedAtFormatted) {
            $warning = true;
        }

        $canedit = $snapshot->author_id == Auth::user()->id ? true : false;

        return view('snapshot.page', [
            'url' => $url,
            'builddata' => $snapshot,
            'canedit' => $canedit,
            'warning' => $warning
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
            'name.regex' => 'Для названия снапшота используйте только латиницу или спецсимолы.',
            'name.unique' => 'Название снапшота должно быть уникальным.',

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
            $response = 'Снапшот успешно загружен!';
        } else {
            $build = $this->create($request, $url);
            $response = 'Данные снапшота обновлены.';
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
        // Сохраняем медиафайлы
        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $fileName = time() . '_' . $image->getClientOriginalName();
                $filePath = 'storage/projects/media/' . $fileName;

                // Сжимаем изображение с помощью Intervention Image (если это изображение)
                if ($image->getClientOriginalExtension() == 'jpeg' || $image->getClientOriginalExtension() == 'png') {
                    $compressedImage = Image::make($image)->encode('jpg', 80); // Преобразуем в JPEG с качеством 80%
                    Storage::put($filePath, $compressedImage->stream());
                } else {
                    Storage::putFileAs('storage/projects/media', $image, $fileName);
                }

                // Создаем запись о медиафайле
                $media = ProjectMedia::create([
                    'author_id' => Auth::user()->id,
                    'project_id' => $project->id,
                    'snapshot_id' => $snapshot->id,
                    'file_name' => $fileName,
                    'for_download' => false,
                ]);
                // Это изображение не предназначено для скачивания
            }
        }

        if (isset($data['downloadable'])) {
            foreach ($data['downloadable'] as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = 'storage/projects/downloadable/' . $fileName;

                // Сохраняем файл для скачивания
                Storage::putFileAs('storage/projects/downloadable', $file, $fileName);

                // Создаем запись о медиафайле для скачивания
                $media = ProjectMedia::create([
                    'author_id' => Auth::user()->id,
                    'project_id' => $project->id,
                    'snapshot_id' => $snapshot->id,
                    'file_name' => $fileName,
                    'for_download' => true,
                ]);
                // Этот файл предназначен для скачивания
            }
        }

        Project::where('url', $url)->update(['updated_at' => now()]);

        return $snapshot->name;
    }
    private function update(Request $newData, $url)
    {
        // Получаем старые данные снапшота
        $oldSnapshot = Snapshots::where('snapshots.id', $newData->id)
            ->join('projects', 'projects.id', 'snapshots.project_id')
            ->select('snapshots.*', 'projects.author_id', 'projects.id as proj_id')
            ->first();

        // Проверяем доступ к редактированию снапшота
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
            // Обновляем данные снапшота
            $oldSnapshot->update($differences);

            // Обработка медиафайлов (изображений и файлов для скачивания)
            if (isset($newData['images'])) {
                foreach ($newData['images'] as $image) {
                    $fileName = time() . '_' . $image->getClientOriginalName();
                    $filePath = 'storage/projects/media/' . $fileName;

                    // Сжимаем изображение с помощью Intervention Image (если это изображение)
                    if ($image->getClientOriginalExtension() == 'jpeg' || $image->getClientOriginalExtension() == 'png') {
                        $compressedImage = Image::make($image)->encode('jpg', 80); // Преобразуем в JPEG с качеством 80%
                        Storage::put($filePath, $compressedImage->stream());
                    } else {
                        Storage::putFileAs('storage/projects/media', $image, $fileName);
                    }

                    // Создаем запись о медиафайле
                    $media = ProjectMedia::create([
                        'author_id' => Auth::user()->id,
                        'project_id' => $oldSnapshot->proj_id,
                        'snapshot_id' => $oldSnapshot->id,
                        'file_name' => $fileName,
                        'for_download' => false,
                    ]);
                    // Это изображение не предназначено для скачивания
                }
            }

            if (isset($newData['downloadable'])) {
                foreach ($newData['downloadable'] as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = 'storage/projects/downloadable/' . $fileName;

                    // Сохраняем файл для скачивания
                    Storage::putFileAs('storage/projects/downloadable', $file, $fileName);

                    // Создаем запись о медиафайле для скачивания
                    $media = ProjectMedia::create([
                        'author_id' => Auth::user()->id,
                        'project_id' => $oldSnapshot->proj_id,
                        'snapshot_id' => $oldSnapshot->id,
                        'file_name' => $fileName,
                        'for_download' => true,
                    ]);
                    // Этот файл предназначен для скачивания
                }
            }

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
            'builddata' => $snapshot
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
}
