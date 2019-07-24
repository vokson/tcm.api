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

    public function saveListOfTransmittal(Request $request)
    {

        if (!Input::has('items')) {
            return Feedback::getFeedback(1001);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('id', $item)) {
                return Feedback::getFeedback(1008);
            }

            if (!array_key_exists('code_1', $item)) {
                return Feedback::getFeedback(1002);
            }

            if ($item['code_1'] == '') {
                return Feedback::getFeedback(1009);
            }

            if (!array_key_exists('code_2', $item)) {
                return Feedback::getFeedback(1003);
            }

            if (!array_key_exists('revision', $item)) {
                return Feedback::getFeedback(1004);
            }
            if ($item['revision'] == '') {
                return Feedback::getFeedback(1010);
            }

            if (!array_key_exists('class', $item)) {
                return Feedback::getFeedback(1005);
            }

            if (!array_key_exists('title_ru', $item)) {
                return Feedback::getFeedback(1006);
            }

            if (!array_key_exists('title_en', $item)) {
                return Feedback::getFeedback(1007);
            }

            $doc = Doc::find($item['id']);

            if (is_null($doc)) {
                return Feedback::getFeedback(1011);
            }

            $doc->code_1 = $item['code_1'];
            $doc->code_2 = $item['code_2'];
            $doc->revision = $item['revision'];
            $doc->class = $item['class'];
            $doc->title_en = $item['title_en'];
            $doc->title_ru = $item['title_ru'];

            $doc->save();

        }

        return Feedback::getFeedback(0);

    }


}
