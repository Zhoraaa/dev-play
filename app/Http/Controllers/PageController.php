<?php

namespace App\Http\Controllers;

use App\Models\DevTeam;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function projects(Request $request)
    {
        // Заготавливаем запрос, достающий основную информацию по проектам
        $query = Project::leftJoin('users', 'users.id', '=', 'projects.author_id')
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
            ->groupBy('projects.id');

        // В случае гет-запроса на сортировку меняем тип сортировки
        switch ($request->sort) {
            default:
                $column = 'created_at';
                $queue = 'desc';
                break;
            case 2:
                $column = 'created_at';
                $queue = 'asc';
                break;
            case 3:
                $column = 'updated_at';
                $queue = 'desc';
                break;
            case 4:
                $column = 'name';
                $queue = 'asc';
                break;
            case 5:
                $column = 'name';
                $queue = 'desc';
                break;
        }

        // Отсеиваем из запроса теги 
        $tagsRaw = array_filter($request->all(), function ($key) {
            return strpos($key, "tag-") === 0;
        }, ARRAY_FILTER_USE_KEY);
        $selectedTags = array();
        foreach ($tagsRaw as $key => $val) {
            $tagID = str_replace('tag-', '', $key);
            $selectedTags += [$tagID => $tagID];
        }

        // Формируем строку с выбранными тегами
        $tagStr = '';
        foreach ($selectedTags as $tag) {
            $tagStr .= Tag::where('id', $tag)->first()->name . ', ';
        }
        $tagStr = substr($tagStr, 0, -2) . '.';

        // Применяем выборку по тегам
        if (!empty($selectedTags)) {
            $query->whereExists(function ($subQuery) use ($selectedTags) {
                $subQuery->select(DB::raw(1))
                    ->from('tag_to_project_connections')
                    ->whereColumn('tag_to_project_connections.project_id', 'projects.id')
                    ->whereIn('tag_to_project_connections.tag_id', $selectedTags)
                    ->groupBy('tag_to_project_connections.project_id')
                    ->havingRaw('COUNT(DISTINCT tag_to_project_connections.tag_id) = ?', [count($selectedTags)]);
            });
        }

        if ($request->developer) {
            $query->where('users.login', '=', $request->developer);
        }

        // Применяем правила сортировки
        $query->orderBy($column, $queue);

        // Выполняем запрос + пагинация
        $projects = $query->paginate(6);

        // Форматируем текст
        foreach ($projects as $project) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($project->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($project->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $project->formatted_created_at = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
            $project->formatted_updated_at = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";
        }

        $tags = Tag::orderBy('name', 'asc')->get();

        // Отсылаем на представление
        return view('project.list', [
            'projects' => $projects, // Проекты
            'tags' => $tags, // Список всех тегов
            'tagStr' => $tagStr, // Используемые в фильтрации теги
            'selectedTags' => $selectedTags // ID Используемых тегов (Для отметок в списке)
        ]);
    }

    public function news()
    {
        $news = Post::orderBy('created_at', 'desc')
            ->leftJoin('users', 'users.id', 'posts.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', 'posts.author_mask')
            ->where('type_id', '=', 1)
            ->select(
                'posts.*',
                'users.login as author',
                'users.avatar',
                'users.role_id',
                'dev_teams.name as showing_author',
                'dev_teams.url as showing_author_url',
                'dev_teams.avatar as showing_author_avatar',
            )
            ->get();

        foreach ($news as $post) {
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

        // dd($news);
        return view('newslist', [
            'news' => $news,
            'buglist' => false,
        ]);
    }

    public function devTeams()
    {
        $teams = DevTeam::orderBy('name', 'asc')->get();
        foreach ($teams as $team) {
            // Форматирование даты и времени создания (created_at)
            $createdAt = Carbon::parse($team->created_at);
            $createdAtFormatted = $createdAt->format('d/m/Y H:i');
            $createdAtDiff = $createdAt->diffForHumans();

            // Форматирование даты и времени обновления (updated_at)
            $updatedAt = Carbon::parse($team->updated_at);
            $updatedAtFormatted = $updatedAt->format('d/m/Y H:i');
            $updatedAtDiff = $updatedAt->diffForHumans();

            // Формируем окончательные строки для отображения
            $team->formatted_created_at = "$createdAtDiff <i class='text-secondary'>($createdAtFormatted)</i>";
            $team->formatted_updated_at = "$updatedAtDiff <i class='text-secondary'>($updatedAtFormatted)</i>";
        }


        return view('devteams', [
            'devteams' => $teams,
        ]);
    }

    public function devs()
    {
        $devs = User::where('role_id', '=', 2)
            ->orderBy('login')
            ->get();

        return view('devlist', [
            'devs' => $devs,
        ]);
    }
}
