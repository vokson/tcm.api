<?php

use Illuminate\Database\Seeder;
use App\Setting;
use App\Status;

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
            'COUNT_OF_ITEMS_IN_NEWS' => '20',
            'ARCHIVE_STORAGE_TIME' => '1800',
            'ARCHIVE_CREATION_TIME' => '300',
            'RATING_MISTAKE_COUNT' => '12',
            'RATING_MISTAKE_PROBABILITY' => '0.95',
            'RATING_INITIAL_VALUE' => '0.5',
            'LAST_TASK_NUMBER' => '001',
            'CREATE_TASK_NOTIFICATION' => 'Создан TASK',
            // Одинарное экранирование, так как это PHP регулярное выражение
            'CHECKER_REG_EXP_FOR_NEW_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'CHECKER_REG_EXP_FOR_CHECKED_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\[\d+\]\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'DOCS_REG_EXP_FOR_LIST_FILE' => '/.+\.json$/',
            'TRANSMITTAL_REG_EXP' => '/^(4022-CM-RH-T-|4022-RH-CM-T-)\d{3}[1-9]{1}$/',
            'DOCS_REG_EXP_FOR_PDF_FILE' => '/.*(pdf|PDF)$/',
            'STATUS_ID_FOR_NEW_TRANSMITTAL' => Status::where('name', 'TRANSMITTAL')->first()->id,
            'SENDER_FOLDER_NOTIFICATION_SUBJECT' => 'ВОЛГАФЕРТ. ОТПРАВКА.',
            'FRONTEND_PROJECT_NAME_RU' => 'Проектный офис ВОЛГАФЕРТ',
            'FRONTEND_PROJECT_NAME_EN' => 'Design office VOLGAFERT',
            'FRONTEND_CODE_1_NAME_RU' => 'КОД ДОКУМЕНТА 1',
            'FRONTEND_CODE_1_NAME_EN' => 'DOC CODE 1',
            'FRONTEND_CODE_2_NAME_RU' => 'КОД ДОКУМЕНТА 2',
            'FRONTEND_CODE_2_NAME_EN' => 'DOC CODE 2',
            // Двойное экранирование, так как это уже JS регулярное выражение
            'FRONTEND_DOCS_CODE_1_JS_REG_EXP' => '^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\\d{4}|\\d{2}-\\d{3}|\\d{1}-\\d{3}(\\s[A-Z]\\d?)?)(_C_RH_(OP|CL))?$',
            'FRONTEND_DOCS_CODE_2_JS_REG_EXP' => '^7500081106-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)-(КМ|КЖ|АР|ЭГ|НВК|НВК|ОВ)\\d{0,2}(\\.(РР|ТИ|ТЗ))?-\\d{4}$',
//            'FRONTEND_DOCS_REV_JS_REG_EXP' => '^\\d{1}[A-Z0-9]{1}$',
            'DOCS_REV_LIST' => '0A|0B|0C|0D|0E|0F|00|1A|1B|1C|1D|1E|1F|01|2A|2B|2C|2D|2E|2F',
            'FRONTEND_DOCS_CLASS_JS_REG_EXP' => '^(A|C|I|RQ|FI|IFC)$',
            'FRONTEND_RECORDS_ADD_WORDS_TO_TEXT_IN_JSON' => '["Здесь должны","быть слова","которые можно добавить"]',
            'FRONTEND_TITLES_FIELD_1_NAME_RU' => 'Предшественник',
            'FRONTEND_TITLES_FIELD_1_NAME_EN' => 'Predecessor',
            'FRONTEND_TITLES_FIELD_2_NAME_RU' => 'Описание',
            'FRONTEND_TITLES_FIELD_2_NAME_EN' => 'Description',
            'FRONTEND_TITLES_FIELD_3_NAME_RU' => 'Объем',
            'FRONTEND_TITLES_FIELD_3_NAME_EN' => 'Volume',
            'FRONTEND_DOCS_JSON_MAX_FILE_SIZE_MB' => '1',
            'FRONTEND_RECORDS_MAX_FILE_SIZE_MB' => '1000',
            'FRONTEND_CHECKER_MAX_FILE_SIZE_MB' => '50',
            'FRONTEND_SENDER_MAX_FILE_SIZE_MB' => '1000',
            'FRONTEND_MERGE_PDF_MAX_FILE_SIZE_MB' => '100',
            // Четверное экранирование, так как сначала парсится JSON, а потом регулярное выражение передается в PHP
            'FRONTEND_CHARTS_TITLES_PHP_REG_EXP_IN_JSON' => '[{"key": "All / Все", "value": "/.*/"}, {"key": "KJ*.DRW", "value": "/.*[-]{1}KJ[\\\\d]*\\\\.DRW$/"}]',
        ];

        foreach ($settings as $key => $value) {
            $action = Setting::create(['name' => $key, 'value' => $value]);
            $action->save();
        }
    }
}
