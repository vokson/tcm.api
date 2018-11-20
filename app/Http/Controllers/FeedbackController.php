<?php

namespace App\Http\Controllers;

class FeedbackController extends Controller
{
    private static $errors = [
        // Authentication
        101 => 'Authentication failed. Incorrect login\password',
        102 => 'Token is expired',
        103 => 'Invalid token',
        104 => 'Permission denied',
        105 => 'Wrong new password',
        106 => 'Can not pass regular expression middleware',
        // Admin
        201 => 'Wrong setting name',
        202 => 'Wrong setting value',
        203 => 'Settings are missed',
        204 => 'Items are missed',
        205 => 'Wrong setting id',
        206 => 'Foreign key exception',
        //Log
        301 => 'Wrong input To',
        302 => 'Wrong input From',
        303 => 'Wrong input Title',
        304 => 'Wrong input What',
        305 => 'Wrong input Id',
        306 => 'Wrong input Date',
        307 => 'Wrong input Is New',
        //Title
        401 => 'Wrong input Id',
        402 => 'Wrong input Name',
        403 => 'Wrong input Status',
        404 => 'Wrong input Predecessor',
        //User
        501 => 'Wrong input Id',
        502 => 'Wrong input E-mail',
        503 => 'Wrong input Surname',
        504 => 'Wrong input Name',
        505 => 'Wrong input Role',
        506 => 'Wrong input Active',
        507 => 'Wrong input Permission Expression',
        //File
        601 => 'File is missed',
        602 => 'Badly uploaded',
        603 => 'File can not be deleted',
        604 => 'Wrong input Id',
        605 => 'Wrong input Uin',
        606 => 'Error of file storage',
        607 => 'File can not be saved'
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
