<?php

use Illuminate\Database\Migrations\Migration;
use App\Action;
use Illuminate\Support\Facades\DB;

class CreateTableActionsByRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $admin_urls = [
            'auth/login',
            'auth/login/token',
            'auth/check_token',
        ];

        $urls = [
            'auth/change_password',
            "logs/get",
            "logs/set",
            "logs/delete",
            "titles/get",
            "titles/history/get",
            "users/get",
            "statuses/get",
            "logs/file/upload",
            "logs/file/download",
            "logs/file/download/all",
            "logs/file/get",
            "logs/file/delete",
            "logs/new/message/switch",
            "logs/new/message/count",
            "logs/get/last/articles",
            "charts/logs/created/get",
            "charts/titles/created/get",
            "charts/titles/status/get",
            "charts/tq/status/get",
            "charts/storage/get",
            "charts/checked/drawings/get",
            "checker/get",
            "checker/delete",
            "checker/rating/get",
            "checker/file/upload",
            "checker/file/download",
            "checker/file/download/all",
            "sender/folder/add",
            "sender/folder/get",
            "sender/folder/delete",
            "sender/folder/count",
            "sender/folder/switch/ready",
            "sender/file/upload",
            "sender/file/get",
            "sender/file/delete",
            "sender/file/download",
            "sender/file/download/all",
            "merge/pdf/get",
            "merge/pdf/clean",
            "merge/pdf/set/main/name",
            "merge/pdf/file/upload",
            "merge/pdf/file/download",
            'settings/user/get',
            'settings/user/set',
            'task/create',
            'docs/search/get',
            'counts',
            "titles/set",
            "titles/delete",
            "docs/edit/get",
            "docs/edit/set",
            "docs/edit/add",
            "docs/edit/delete",
            "docs/edit/file/upload",
            "settings/get",
            "settings/set",
            "statuses/set",
            "statuses/add",
            "statuses/delete",
            "users/set",
            "users/set/default/password",
            "users/delete",
            'service/database/backup',
            'service/database/update/attachments',
            'logs/clean/files/without/articles',
            'action/set',
            'action/get'
        ];

        foreach ($urls as $url) {
            $action = new Action(['name' => $url]);
            $action->save();
        }

        foreach ($admin_urls as $url) {
            $action = new Action(['name' => $url]);
            $action->save();
            $action = new Action(['name' => $url, 'role' => 'admin']);
            $action->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('actions')->truncate();
    }
}
