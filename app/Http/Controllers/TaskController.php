<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Title;
use App\Http\Controllers\UserController;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        if (!Title::where('id', '=', Input::get('title'))->exists()) {
            return Feedback::getFeedback(303);
        }

        $task_id = $this->createFolder();

        if ($task_id == -1) {
            return Feedback::getFeedback(611);
        }

        $owner_id = UserController::getUserId($request);

        $log = new Log;

        $log->owner = $owner_id;
        $log->is_new = true;
        $log->to = $owner_id;
        $log->from = $owner_id;
        $log->title = $request->input('title');
        $log->what = SettingsController::take('CREATE_TASK_NOTIFICATION') + ' ' + $task_id;

        $log->save();

        return Feedback::getFeedback(0);
    }

    private function createFolder()
    {
        $id = SettingsController::take('LAST_TASK_NUMBER') + 1;

        try {

            $result = Storage::makeDirectory(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'TASKS' . DIRECTORY_SEPARATOR .
                ServiceController::createFolderForFileByNumber($id) . DIRECTORY_SEPARATOR .
                'TASK ' . $id
            );

        } catch (QueryException $e) {
            return false;
        }

        if ($result) {
            SettingsController::save('LAST_TASK_NUMBER', $id);
            return $id;

        } else {
            return -1;
        }

    }
}
