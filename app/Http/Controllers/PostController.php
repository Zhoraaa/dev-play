<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostMedia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator;

class PostController extends Controller
{
    //
    public function index($id)
    {
        $post = Post::where('posts.id', '=', $id)
            ->leftJoin('users', 'users.id', 'posts.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', 'posts.author_mask')
            ->leftJoin('post_media', 'post_media.post_id', '=', 'posts.id')
            ->select(
                'posts.*',
                'users.login as author',
                'users.avatar',
                'dev_teams.name as showing_author',
                'dev_teams.url as showing_author_url',
                DB::raw('GROUP_CONCAT(post_media.file_name) as media_files')
            )
            ->groupBy('posts.id', 'users.login', 'users.avatar', 'dev_teams.name', 'dev_teams.url')
            ->first();

        if (!$post) {
            return redirect()->back()->with('error', 'Пост не найден');
        }
        // Форматирование даты и времени создания (created_at)
        $createdAt = Carbon::parse($post->created_at);
        $createdAtFormatted = $createdAt->format('d/m/Y H:i');
        $createdAtDiff = $createdAt->diffForHumans();

        // Форматирование даты и времени обновления (updated_at)
        $updatedAt = Carbon::parse($post->updated_at);
        $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
        $updatedAtDiff = $updatedAt->diffForHumans();

        // Формируем окончательные строки для отображения
        $post->created_at_formatted = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
        $post->updated_at_formatted = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";


        $canedit = false;
        if (isset(Auth::user()->id) && $post->author === Auth::user()->login) {
            $canedit = true;
        }

        $comms = Comment::orderBy('created_at', 'asc')
            ->where('post_id', '=', $post->id)
            ->leftJoin('users', 'users.id', 'comments.author_id')
            ->select(
                'comments.*',
                'users.login as author',
                'users.avatar',
            )
            ->get();

        $commsCount = Comment::where('post_id', '=', $post->id)->count();

        foreach ($comms as $comm) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($post->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($post->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $comm->created_at_formatted = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
            $comm->updated_at_formatted = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";
        }

        return view('post.page', [
            'post' => $post,
            'comms' => $comms,
            'canedit' => $canedit,
            'commsCount' => $commsCount,
        ]);
    }
    public function save(Request $request, $from_team, $team)
    {
        $validator = Validator::make([
            $request->all(),
            'text' => 'required', // Обязательное поле для описания
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Разрешенные типы файлов и максимальный размер
        ], [
            'text.required' => 'Поле "Описание" обязательно для заполнения.',
            'cover.image' => 'Файл должен быть изображением.',
            'cover.mimes' => 'Поддерживаемые форматы изображений: jpeg, png, jpg, gif.',
            'cover.max' => 'Максимальный размер изображения: 2048 КБ.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->id) {
            $this->update($request);
            $string = 'Данные обновлены.';
        } else {
            $this->create($request, $from_team, $team);
            $string = 'Пост сохранён.';
        }
        return redirect()->back()->with('success', $string);
    }

    public function editor($url)
    {
        $count = Post::where('url', $url)->count();
        if ($count) {
            $postdata = Post::where('url', $url)->first();
            $postdata->text = $this->removeLinks($postdata->text);
            $postdata->text = str_replace("<b>", "**", $postdata->text);
            $postdata->text = str_replace("</b>", "**", $postdata->text);
            $postdata->text = str_replace("<i>", "_", $postdata->text);
            $postdata->text = str_replace("</i>", "_", $postdata->text);
            $postdata->text = str_replace("<br>", "\r\n", $postdata->text);

            return view('post.editor', [
                'text' => $postdata->text,
            ])->with('warning', 'Вы заходите на опасную территорию.');
        }
        return redirect()->route('home')->with('error', 'Проект не найден.');
    }

    private function create($data, $from_team, $team)
    {
        // Обработка текста по ключ. символам
        $data->text = strip_tags($data->text);
        $data->text = $this->handle($data->text, '**');
        $data->text = $this->handle($data->text, '_');
        $data->text = str_replace("\r\n", "<br>", $data->text);
        $data->text = $this->handleLinks($data->text);

        // Применение маски авторства в зависимости от пришедших данных
        $author_mask = $from_team ? $team : null;

        // Запись причастного проекта
        $projID = $data->projID ?? null;

        // Запись о новом опсте
        $post = Post::create([
            'author_id' => Auth::user()->id,
            'author_mask' => $author_mask,
            'for_project' => $projID,
            'show_true_author' => 1,
            'text' => $data->text,
            'type_id' => 1,
        ]);

        $this->multiloadMedia($data, $post->id);
    }

    public function destroy($id)
    {
        Comment::where('post_id', $id)->delete();
        PostMedia::where('post_id', $id)->delete();
        Post::where('id', $id)->delete();

        return redirect()->route('news')->with('success', 'Пост удалён.');
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
    private function multiloadMedia($data, $post_id)
    {
        if ($data->hasFile('media')) {
            $files = $data->file('media');
            $fileNames = [];

            foreach ($files as $file) {
                // Генерация имени файла
                $fileName = 'post_' . $post_id . '_' . $file->getClientOriginalName();
                $mediaPath = $file->storeAs('public/imgs/posts/media/', $fileName);

                // Сохранение пути файла для дальнейшего использования
                $fileNames[] = $fileName;

                // Сохранение в базе данных
                PostMedia::create([
                    'post_id' => $post_id,
                    'author_id' => Auth::user()->id,
                    'file_name' => $fileName,
                    'created_at' => false
                ]);
            }
        }
    }
}
