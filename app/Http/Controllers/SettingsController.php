<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Input;

class SettingsController extends Controller
{
    public static function take($name)
    {
        $parameter = Setting::where('name', $name)->first();

        if ($parameter) {
            return $parameter->value;
        }
    }

    public function get(Request $request)
    {
        $settings = Setting::all();
        $parameters = [];


        foreach ($settings as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'name' || $k == 'value');
            }, ARRAY_FILTER_USE_KEY);
        }

        return Feedback::getFeedback(0, [
            "settings" => $parameters
        ]);


    }

    public function set(Request $request)
    {

        if (!Input::has('settings')) {
            return Feedback::getFeedback(203);
        }

        $settings = $request->input('settings');

        foreach ($settings as $item) {

            if (!array_key_exists('name', $item)) {
                return Feedback::getFeedback(201);
            }

            if (!array_key_exists('value', $item)) {
                return Feedback::getFeedback(202);
            }

            $name = $item['name'];
            $value = $item['value'];

            $parameter = Setting::where('name', $name)->first();

            if ($parameter) {

                $parameter->fill([
                    'name' => $name,
                    'value' => $value
                ]);

                $parameter->save();


            } else {

                return Feedback::getFeedback(201);
            }


        }

        return Feedback::getFeedback(0);

    }
}
