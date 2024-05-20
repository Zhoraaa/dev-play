<?php

namespace App\Http\Controllers;

use App\Models\DevTeam;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function home()
    {
        $projects = Project::leftJoin('users', 'users.id', '=', 'projects.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', '=', 'projects.team_rights_id')
            ->leftJoin('tag_to_project_connections', 'projects.id', '=', 'tag_to_project_connections.project_id')
            ->leftJoin('tags', 'tags.id', '=', 'tag_to_project_connections.tag_id')
            ->select(
                'projects.*',
                'users.avatar',
                'users.role_id',
                'users.login as author',
                'dev_teams.name as dev_team',
                DB::raw('GROUP_CONCAT(DISTINCT tags.name ORDER BY tags.name SEPARATOR ", ") as tags')
            )
            ->groupBy('projects.id')
            ->orderBy('updated_at', 'asc')
            ->get(5);

        $teams = DevTeam::orderBy('name', 'asc')->get();

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
            )
            ->get(5);

        return view('home', [
            'projects' => $projects,
            'teams' => $teams,
            'posts' => $posts,
        ]);
    }

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
                'dev_teams.name as dev_team',
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
                $queue = 'asc';
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
            $project->formatted_created_at = $project->created_at->format('d.m.Y H:i');
            $project->formatted_updated_at = $project->updated_at->format('d.m.Y H:i');
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

    public function news(Request $request)
    {
        $news = Post::orderBy('created_at', 'desc')
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
            )
            ->get();

        foreach ($news as $post) {
            $post->formatted_created_at = $post->created_at->format('d.m.Y H:i');
            $post->formatted_updated_at = $post->updated_at->format('d.m.Y H:i');
        }

        return view('newslist', [
            'news' => $news
        ]);
    }

    public function devTeams()
    {
        $teams = DevTeam::orderBy('name', 'asc')->get();

        return view('devteams', [
            'devteams' => $teams,
        ]);
    }
}
