<?php

namespace App\Http\Controllers;

use App\ApiUser;
use Illuminate\Http\Request;
use DateTime;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Http\Controllers\SettingsController As Settings;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = ApiUser::where('email', $email)->where('password', $password)->first();

        if ($user && $user->active == true) {

            $token = bin2hex(random_bytes(30));
            $user->access_token = $token;
            $user->save();

            return Feedback::getFeedback(0, [
                'access_token' => $user->access_token,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'email' => $user->email,
                'id' => $user->id
            ]);

        } else  return Feedback::getFeedback(101);

    }


    public function loginByToken(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        if ($user && $user->active == true && $token != "") {

            $interval = strtotime('now') - strtotime($user->updated_at);
            if ($interval > Settings::take('TOKEN_LIFE_TIME')) return Feedback::getFeedback(102);

            return Feedback::getFeedback(0, [
                'access_token' => $user->access_token,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'email' => $user->email,
                'id' => $user->id
            ]);

        } else  return Feedback::getFeedback(102);

    }

    public static function isTokenValid(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        if ($user && $token != "") {

            $interval = strtotime('now') - strtotime($user->updated_at);
            if ($interval > Settings::take('TOKEN_LIFE_TIME')) return Feedback::getFeedback(102);

            return Feedback::getFeedback(0);
        }

        return Feedback::getFeedback(103);

    }

    public function getListOfUsers(Request $request)
    {
        $users = ApiUser::all();
        $parameters = [];


        foreach ($users as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'id' || $k == 'name' || $k == 'surname');
            }, ARRAY_FILTER_USE_KEY);
        }

        // Сортируем массив по фамилии, затем по имени
        usort($parameters, array($this, "cmp"));

        return Feedback::getFeedback(0, [
            "items" => $parameters
        ]);

    }

    private function cmp($a, $b)
    {
        $result = strcmp($a['surname'], $b['surname']);
        return ($result == 0) ? strcmp($a['name'], $b['name']) : $result;

    }
}
