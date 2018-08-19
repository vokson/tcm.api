<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Title;
use App\Http\Controllers\FeedbackController As Feedback;

class TitleController extends Controller
{
    public function get(Request $request)
    {
        $titles = Title::all();
        $parameters = [];


        foreach ($titles as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'id' || $k == 'name');
            }, ARRAY_FILTER_USE_KEY);
        }

        return Feedback::getFeedback(0, [
            "items" => $parameters
        ]);


    }
}
