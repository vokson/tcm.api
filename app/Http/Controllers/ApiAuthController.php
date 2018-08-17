<?php

namespace App\Http\Controllers;

use App\ApiUser;
use Illuminate\Http\Request;
use DateTime;
use App\Http\Controllers\FeedbackController As Feedback;

class ApiAuthController extends Controller
{
    private static $tokenLifeTime = 10000;

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = ApiUser::where('email', $email)->where('password', $password)->first();

        if ($user->active == true) {

            $token = bin2hex(random_bytes(30));
            $user->access_token = $token;
            $user->save();

            return Feedback::getFeedback(0, ['access_token' => $user->access_token]);

        } else  return Feedback::getFeedback(101);

    }

    public static function isTokenValid(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        if ($user && $token != "") {

            $interval = strtotime('now') - strtotime($user->updated_at);
            if ($interval > self::$tokenLifeTime) return Feedback::getFeedback(102);

            return Feedback::getFeedback(0);
        }

        return Feedback::getFeedback(103);

    }

    public function test(Request $request)
    {
        return Feedback::getFeedback(0, ['text'=> 'TEST FUNCTION IS WORKING']);
    }
}
