<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Validator;

class TagController extends Controller
{
    //
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tags'
        ], [
            'name.required' => 'Вы не вписали название тега',
            'name.unique' => 'Такой тег уже есть'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Tag::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Добавлен новый тег: "' . $request->name . '".');
    }

    public function destroy($id)
    {
        Tag::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Тег успешно удалён.');
    }
}
