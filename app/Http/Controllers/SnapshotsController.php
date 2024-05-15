<?php

namespace App\Http\Controllers;

use App\Models\Snapshots;
use Illuminate\Http\Request;

class SnapshotsController extends Controller
{
    //
    public function index($url)
    {
        return view('snapshot.page', [
            'url' => $url
        ]);
    }
    public function save(Request $request, $url)
    {
        if (isset($request->id)) {
            $build = $this->update($request);
            $response = 'Снапшот успешно загружен!';
        } else {
            $build = $this->create($request);
            $response = 'Данные снапшота обновлены.';
        }
        return redirect()->route('snapshot', [
            'url' => $url,
            'build' => $build,
        ])->with('success', $response);
    }
    public function create($data)
    {
    }
    public function update($newData)
    {
    }
    public function editor()
    {
    }
    public function destroy($url, $name)
    {
        Snapshots::join('projects', 'projects.id', 'snapshots.project_id')
            ->where('');
    }
}
