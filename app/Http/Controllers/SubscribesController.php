<?php

namespace App\Http\Controllers;

use App\Models\Subscribes;
use Auth;
use Illuminate\Http\Request;

class SubscribesController extends Controller
{
    //
    public function subs($type, $id)
    {
        $sub = Subscribes::where('sub_type', '=', $type)
            ->where('sub_for', '=', $id)
            ->where('subscriber_id', '=', Auth::user()->id)
            ->first();

        if (!isset($sub)) {
            Subscribes::create([
                'sub_type' => $type,
                'sub_for' => $id,
                'subscriber_id' => Auth::user()->id,
            ]);

            $string = 'Подписка оформлена.';
        } else {
            Subscribes::where('sub_type', '=', $type)
                ->where('sub_for', '=', $id)
                ->where('subscriber_id', '=', Auth::user()->id)
                ->delete();

            $string = 'Вы успешно отписались.';
        }

        return redirect()->back()->with('success', $string);
    }
}
