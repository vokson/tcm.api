<?php

namespace App\Http\Controllers;

class FeedbackController extends Controller
{
    private static $errors = [
        // Authentication 1xx
        101 => 'Authentication failed. Incorrect login\password',
        102 => 'Token is expired',
        103 => 'Invalid token',
        104 => 'Permission denied',
        // Admin
        201 => 'Wrong setting name',
        202 => 'Wrong setting value',
        203 => 'Settings are missed',
        //Log
        301 => 'Wrong input To',
        302 => 'Wrong input From',
        303 => 'Wrong input Title',
        304 => 'Wrong input What',
    ];

    public static function getFeedback($errorCode = 0, $arr = [])
    {

        $feedback = $arr;

        if ($errorCode === 0) {

            $feedback['success'] = 1;
            return json_encode($feedback);

        } else {

            $feedback['success'] = 0;
            $feedback['error'] = $errorCode;
            $feedback['description'] = self::$errors[$errorCode];
            return json_encode($feedback);

        }

    }
}
