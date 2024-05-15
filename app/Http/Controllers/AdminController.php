<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //

    public function tagList()
    {
        $tags = Tag::orderBy('name', 'asc')->get();

        return view('admin.taglist', ['tags' => $tags]);
    }
}
