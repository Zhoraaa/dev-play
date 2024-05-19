<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    //
    public function index($id)
    {
        $post = Post::where('posts.id', '=', $id)
            ->leftJoin('users', 'users.id', 'posts.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', 'posts.author_mask')
            ->select(
                'posts.*',
                'users.login as author',
                'users.avatar',
                'dev_teams.name as showing_author',
                'dev_teams.url as showing_author_url',
            )
            ->first();

        $post->formatted_created_at = $post->created_at->format('d.m.Y H:i');
        $post->formatted_updated_at = $post->updated_at->format('d.m.Y H:i');

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
            $comm->formatted_created_at = $comm->created_at->format('d.m.Y H:i');
            $comm->formatted_updated_at = $comm->updated_at->format('d.m.Y H:i');
        }

        return view('post.page', [
            'post' => $post,
            'comms' => $comms,
            'canedit' => $canedit,
            'commsCount' => $commsCount,
        ]);
    }
    public function save(Request $request)
    {
        $request->validate([
            'text' => 'required', // Обязательное поле для описания
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Разрешенные типы файлов и максимальный размер
        ], [
            'text.required' => 'Поле "Описание" обязательно для заполнения.',
            'cover.image' => 'Файл должен быть изображением.',
            'cover.mimes' => 'Поддерживаемые форматы изображений: jpeg, png, jpg, gif.',
            'cover.max' => 'Максимальный размер изображения: 2048 КБ.',
        ]);

        if ($request->id) {
            $this->update($request);
            $string = 'Данные обновлены.';
        } else {
            $this->create($request);
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

    private function create($data)
    {
        $data->text = strip_tags($data->text);
        $data->text = $this->handle($data->text, '**');
        $data->text = $this->handle($data->text, '_');
        $data->text = str_replace("\r\n", "<br>", $data->text);
        $data->text = $this->handleLinks($data->text);

        $proj = Post::create([
            'author_id' => Auth::user()->id,
            'author_mask' => null,
            'for_project' => null,
            'show_true_author' => 1,
            'text' => $data->text,
            'type_id' => 1,
        ]);
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
        $oldData = Post::where('id', $newData->id)->first();

        // В случае успешной проверки безопасности работаем дальше
        if (Auth::user()->id == $oldData->author_id) {
            // Преобразовываем текст описания по ключевым символам
            $newDataText['text'] = strip_tags($newDataText['text']);
            $newDataText['text'] = $this->handle($newDataText['text'], '**');
            $newDataText['text'] = $this->handle($newDataText['text'], '_');
            $newDataText['text'] = str_replace("\r\n", "<br>", $newDataText['text']);
            $newDataText['text'] = $this->handleLinks($newDataText['text']);
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
            return redirect()->back()->with('success', 'Данные сохранены');
        } else {
            return redirect()->back()->with('error', 'Отказано в доступе');
        }
    }

    public function destroy($id)
    {
        Post::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Пост удалён.');
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
