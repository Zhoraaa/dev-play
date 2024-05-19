<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    //Удаление комментария
    public function destroy($id)
    {
        Comment::where('id', '=', $id)->delete();

        return redirect()->back()->with('success', 'Комментарий удалён');
    }

    // Создание комментария
    public function create(Request $request, $post_id)
    {
        $request->text = strip_tags($request->text);
        $request->text = $this->handle($request->text, '**');
        $request->text = $this->handle($request->text, '_');
        $request->text = str_replace("\r\n", "<br>", $request->text);
        $request->text = $this->handleLinks($request->text);

        $comment = Comment::create([
            'author_id' => Auth::user()->id,
            'post_id' => $post_id,
            'text' => $request->text
        ]);

        return redirect()->back()->with('success', 'Комментарий опубликован.');
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
}
