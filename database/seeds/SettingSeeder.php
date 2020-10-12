<?php

use Illuminate\Database\Seeder;
use App\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            'TOKEN_LIFE_TIME' => '43200',
            'DEFAULT_PASSWORD' => '1234',
//            'SYSTEM_USER_ID' => \App\ApiUser::where('email', 'guest@mail.com')->first()->id, //59
            'COUNT_OF_ITEMS_IN_NEWS' => '20',
            'ARCHIVE_STORAGE_TIME' => '1800',
            'ARCHIVE_CREATION_TIME' => '300',
            'RATING_MISTAKE_COUNT' => '12',
            'RATING_MISTAKE_PROBABILITY' => '0.95',
            'RATING_INITIAL_VALUE' => '0.5',
            'LAST_TASK_NUMBER' => '001',
            'CREATE_TASK_NOTIFICATION' => 'Создан TASK',
            'CHECKER_REG_EXP_FOR_NEW_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'CHECKER_REG_EXP_FOR_CHECKED_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\[\d+\]\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'DOCS_REG_EXP_FOR_LIST_FILE' => '/.+\.json$/',
            'TRANSMITTAL_REG_EXP' => '/^(4022-CM-RH-T-|4022-RH-CM-T-)\d{3}[1-9]{1}$/',
            'DOCS_REG_EXP_FOR_PDF_FILE' => '/.*(pdf|PDF)$/',
            'STATUS_ID_FOR_NEW_TRANSMITTAL' => \App\Status::where('name', 'TRANSMITTAL')->first()->id, //12
            'SENDER_FOLDER_NOTIFICATION_SUBJECT' => 'ВОЛГАФЕРТ. ОТПРАВКА.'
        ];

        foreach ($settings as $key => $value) {
            $action = Setting::create(['name' => $key, 'value' => $value]);
            $action->save();
        }
    }
}
