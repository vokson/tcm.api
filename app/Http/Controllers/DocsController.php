<?php

namespace App\Http\Controllers;

use App\Doc;
use App\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;

class DocsController extends Controller
{
    public function getListOfTransmittal(Request $request)
    {

        $transmittal_name = trim(Input::get('transmittal', ''));

        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        $items = Doc::where('transmittal', $transmittal->id)->get();

        return Feedback::getFeedback(0, [
            'items' => $items->toArray()
        ]);

    }
}
