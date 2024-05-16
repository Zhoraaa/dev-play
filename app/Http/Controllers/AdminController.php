<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //

    public function tagList()
    {
        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.taglist', ['tags' => $tags]);
    }

    public function userList()
    {
        $users = User::join('roles', 'roles.id', 'users.role_id')
            ->select('users.*', 'roles.name as role')
            ->get();

        return view('admin.userlist', [
            'users' => $users,
        ]);
    }

    public function userEdit(Request $request, $id)
    {
        if (isset($request->changeRole)) {
            User::where('id', '=', $id)
                ->update(['role_id' => $request->changeRole]);
            $string = 'Роль изменена.';
        }
        if (isset($request->ban)) {
            User::where('id', '=', $id)
                ->update(['banned' => $request->ban]);
            $string = 'Пользователь ';
            $string .= $request->ban === 1 ? 'забанен' : 'разбанен';
        }

        return redirect()->back()->with('success', $string);
    }
}
