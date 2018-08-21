<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Status;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\QueryException;

class StatusController extends Controller
{
    public function add()
    {
        $item = new Status();
        $item->name = 'NONAME' . ' - ' . uniqid();
        $item->save();

        return Feedback::getFeedback(0);
    }

    public function delete(Request $request)
    {

        if (!Input::has('id')) {
            return Feedback::getFeedback(205);
        }

        if (!Status::where('id', '=', Input::get('id'))->exists()) {
            return Feedback::getFeedback(205);
        }

        $item = Status::find($request->input('id'));
        $item->delete();

        return Feedback::getFeedback(0);
    }


    public function get(Request $request)
    {
        $parameters = [];

        foreach (Status::all() as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'id' || $k == 'name');
            }, ARRAY_FILTER_USE_KEY);
        }

        return Feedback::getFeedback(0, [
            "items" => $parameters
        ]);


    }

    public function set(Request $request)
    {

        if (!Input::has('items')) {
            return Feedback::getFeedback(204);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('id', $item)) {
                return Feedback::getFeedback(205);
            }

            if (!array_key_exists('name', $item)) {
                return Feedback::getFeedback(201);
            }

            $name = $item['name'];
            $id = $item['id'];

            $parameter = Status::find($id);

            if ($parameter) {

                $parameter->name = $name;

                try {
                    $parameter->save();

                } catch (QueryException $ex) {
                    // В случае, если name не уникально
                    return Feedback::getFeedback(201);
                }

            } else {

                return Feedback::getFeedback(205);
            }

        }

        return Feedback::getFeedback(0);

    }
}
