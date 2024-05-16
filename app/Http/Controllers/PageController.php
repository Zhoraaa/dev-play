<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //

    public function projects(Request $request)
    {
        $projects = Project::leftJoin('users', 'users.id', '=', 'projects.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', '=', 'projects.team_rights_id')
            ->select('projects.*', 'users.login as author', 'dev_teams.name as dev_team')
            ->paginate(3);

        // dd($projects);

        return view('project.list', ['projects' => $projects]);
    }

    public function news()
    {
        $news = Post::orderBy('created_at', 'asc')
            ->where('type_id', '=', 1)
            ->leftJoin('users', 'users.id', 'posts.author_id')
            ->leftJoin('dev_teams', 'dev_teams.id', 'posts.author_mask')
            ->select(
                'posts.*',
                'users.login as author',
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
}
