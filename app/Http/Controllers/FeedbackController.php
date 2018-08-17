<?php

namespace App\Http\Controllers;

class FeedbackController extends Controller
{
    private static $errors = [
        101 => 'Authentication failed. Incorrect login\password',
        102 => 'Token is expired',
        103 => 'Invalid token',
        104 => 'Permission denied'
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
