<?php

namespace App\Http\Controllers;

use App\Models\Project;
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
}
