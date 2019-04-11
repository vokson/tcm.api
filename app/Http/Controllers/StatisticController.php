<?php

namespace App\Http\Controllers;

use App\Status;
use App\TitleHistoryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\DB;
use DateTime;

class StatisticController extends Controller
{

    public function getItemsForLogChart(Request $request)
    {

        $reg_exp = trim(Input::get('regular_expression', ''));
        $interval = intval(trim(Input::get('interval', '')));
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $log_controller = new LogController();
        [$idTitles, $idNamesTitles] = $log_controller->getNamesTitles(''); //TITLE

        $query = DB::table('logs')
            ->select('id', 'title', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $items = $query
            ->orderBy('created_at', 'asc')
            ->get();

        // Подменяем id на значения полей из других таблиц
        $items->transform(function ($item, $key) use ($idNamesTitles) {
            $item->title = $idNamesTitles[$item->title];
            return $item;
        });

        $items = $items->toArray();

        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($items as $key => $value) {
            if (preg_match($reg_exp, $value->title) != 1) {
                unset ($items[$key]);
            }
        }

        return Feedback::getFeedback(0, [
            'items' => $this->divideItemsByIntervalUsingCount($items, $startDate, $endDate, $interval)
        ]);

    }

    public function getItemsForTitleChart(Request $request)
    {
        $reg_exp = trim(Input::get('regular_expression', ''));
        $interval = intval(trim(Input::get('interval', '')));
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $query = DB::table('titles')
            ->select('id', 'name', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $items = $query
            ->orderBy('created_at', 'asc')
            ->get();

        $items = $items->toArray();

        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($items as $key => $value) {
            if (preg_match($reg_exp, $value->name) != 1) {
                unset ($items[$key]);
            }
        }

        return Feedback::getFeedback(0, [
            'items' => $this->divideItemsByIntervalUsingCount($items, $startDate, $endDate, $interval)
        ]);
    }

    private function divideItemsByIntervalUsingCount($items, $startDate, $endDate, $interval)
    {

        $items = array_values($items);
        $countOfIntervals = intdiv(intval($endDate) - intval($startDate), $interval);

        $count = 0;
        $i = 0;

        $labels = [];
        $values = [];

        for ($n = 1; $n <= $countOfIntervals; $n++) {

            while (
                ($i < count($items)) &&
                ($items[$i]->created_at < (intval($startDate) + $n * $interval))
            ) {
                $count++;
                $i++;
            }

            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = $count;
            $count = 0;

        }

        // n выходит из цикла увеличенным на 1

        if ((intval($startDate) + ($n - 1) * $interval) < intval($endDate)) {
            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = count($items) - $i;
        }

        $arr['labels'] = $labels;
        $arr['values'] = $values;

        return $arr;
    }


    public function getItemsForStorageChart(Request $request)
    {
        $storage = trim(Input::get('storage', ''));

        if ($storage !== 'CHECKER_STORAGE' && $storage !== 'LOG_STORAGE') return Feedback::getFeedback(801);
        if ($storage === 'CHECKER_STORAGE') $databaseTableName = 'checked_files';
        if ($storage === 'LOG_STORAGE') $databaseTableName = 'uploaded_files';

        $interval = intval(trim(Input::get('interval', '')));
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $query = DB::table($databaseTableName)
            ->select('id', 'size', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $items = $query
            ->orderBy('created_at', 'asc')
            ->get();

        $items = $items->toArray();

        return Feedback::getFeedback(0, [
            'items' => $this->divideItemsByIntervalUsingValue($items, $startDate, $endDate, $interval, 'size')
        ]);
    }

    private function divideItemsByIntervalUsingValue($items, $startDate, $endDate, $interval, $nameOfValue)
    {

        $items = array_values($items);
        $countOfIntervals = intdiv(intval($endDate) - intval($startDate), $interval);

        $value = 0;
        $i = 0;

        $labels = [];
        $values = [];

        for ($n = 1; $n <= $countOfIntervals; $n++) {

            while (
                ($i < count($items)) &&
                ($items[$i]->created_at < (intval($startDate) + $n * $interval))
            ) {
                $value += $items[$i]->$nameOfValue;
                $i++;
            }

            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = $value;
            $value = 0;

        }

        // n выходит из цикла увеличенным на 1

        if ((intval($startDate) + ($n - 1) * $interval) < intval($endDate)) {
            $labels[] = intval($startDate) + ($n - 1) * $interval;

            $value = 0;
            for ($k = $i; $k < count($items); $k++) {
                $value += $items[$k]->$nameOfValue;
            }

            $values[] = $value;
        }

        $arr['labels'] = $labels;
        $arr['values'] = $values;

        return $arr;
    }

    public function getItemsForTitleStatusChart(Request $request)
    {
        $title_reg_exp = trim(Input::get('title_regular_expression', ''));
        $status_reg_exp = trim(Input::get('status_regular_expression', ''));
        $description_reg_exp = trim(Input::get('description_regular_expression', ''));

        if ($title_reg_exp == '' || $status_reg_exp == '' || $description_reg_exp == '') {
            return Feedback::getFeedback(802);
        }

        $interval = intval(trim(Input::get('interval', '')));
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $query = DB::table('titles_history')
            ->select('id', 'title_id', 'name', 'status', 'description', 'created_at')
            ->where('created_at', '<', $endDate);

        $items = $query
            ->orderBy('created_at', 'asc')
            ->get();

        $items = $items->toArray();


        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($items as $key => $value) {
            if (preg_match($title_reg_exp, $value->name) != 1 || preg_match($description_reg_exp, $value->description) != 1) {
                unset ($items[$key]);
            }
        }

//        return $items;

        $itemsHavingCurrentStatus = [];
        $itemsNotHavingCurrentStatus = [];

        foreach ($items as $key => $value) {

            if (preg_match($status_reg_exp, $value->status) == 1) {
                $itemsHavingCurrentStatus[$value->title_id] = $value->created_at;
            } else {
                if (
                    (isset($itemsHavingCurrentStatus[$value->title_id])) &&
                    ($value->created_at > $itemsHavingCurrentStatus[$value->title_id]) &&
                    (
                        !isset($itemsNotHavingCurrentStatus[$value->title_id]) ||
                        (
                            isset($itemsNotHavingCurrentStatus[$value->title_id]) &&
                            $value->created_at < $itemsNotHavingCurrentStatus[$value->title_id]
                        )
                    )
                ) {
                    $itemsNotHavingCurrentStatus[$value->title_id] = $value->created_at;
                }
            }
        }

        $itemsHavingCurrentStatus = array_sort(array_values($itemsHavingCurrentStatus));
        $itemsNotHavingCurrentStatus = array_sort(array_values($itemsNotHavingCurrentStatus));

        $labels = [];
        $values = [];
        $totalCountOfNotRepliedTitles = 0;

        $max = max($itemsHavingCurrentStatus, $itemsNotHavingCurrentStatus);

        while (count($itemsHavingCurrentStatus) > 0 || count($itemsNotHavingCurrentStatus) > 0) {

            $a = (count($itemsHavingCurrentStatus) > 0 ? $itemsHavingCurrentStatus[0] : $max);
            $b = (count($itemsNotHavingCurrentStatus) > 0 ? $itemsNotHavingCurrentStatus[0] : $max);

            if ($a < $b) {
                $totalCountOfNotRepliedTitles++;
                $labels[] = array_shift($itemsHavingCurrentStatus);
                $values[] = $totalCountOfNotRepliedTitles;
            } else {
                $totalCountOfNotRepliedTitles--;
                $labels[] = array_shift($itemsNotHavingCurrentStatus);
                $values[] = $totalCountOfNotRepliedTitles;
            }

        }

        foreach ($labels as $key => $value) {
            if ($value < $startDate || $value > $endDate) {
                unset($labels[$key]);
                unset($values[$key]);
            }
        }

//        return $items;


        return Feedback::getFeedback(0, [
            'items' => ['labels' => array_values($labels), 'values' => array_values($values)]
        ]);
    }

    public function getItemsForTqStatus(Request $request)
    {
        $title_reg_exp = trim(Input::get('title_regular_expression', ''));
        $description_reg_exp = trim(Input::get('description_regular_expression', ''));

        if ($title_reg_exp == '' || $description_reg_exp == '') {
            return Feedback::getFeedback(802);
        }

        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $items = DB::table('titles_history')
            ->select('id', 'title_id', 'name', 'status', 'predecessor', 'description', 'volume', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['APPROVED', 'REJECTED'])
            ->get()
            ->toArray();


        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($items as $key => $value) {
            if (preg_match($title_reg_exp, $value->name) != 1 || preg_match($description_reg_exp, $value->description) != 1) {
                unset ($items[$key]);
            }
        }

//        return $items;

        $rejected = 0;
        $approvedWithChanges = 0;
        $approvedWithoutChanges = 0;
        $reasonCodes = [0, 0, 0, 0];

        $daysRejected = [];
        $daysApprovedWithChanges = [];
        $daysApprovedWithoutChanges = [];

        $maxDay = 0;

        foreach ($items as $item) {
            $item->predecessor = intval($item->predecessor);
            $item->volume = intval($item->volume);

            $backlogTime = $this->getBacklogTimeForItemInTitleHistory($item->title_id);

            $countOfDays = ($backlogTime === null) ? null : intval(round(($item->created_at - $backlogTime) / 24 / 60 / 60));
            if ($countOfDays > $maxDay) {
                $maxDay = $countOfDays;
            }

            if ($item->status === 'REJECTED') {
                $rejected++;
                $daysRejected = $this->increaseItemOfArray($countOfDays, $daysRejected);
            }

            if ($item->status === 'APPROVED' && $item->predecessor == null) {
                $approvedWithoutChanges++;
                $daysApprovedWithoutChanges = $this->increaseItemOfArray($countOfDays, $daysApprovedWithoutChanges);
            }

            if ($item->status === 'APPROVED' && $item->predecessor != null) {

                if (!is_int($item->volume)) {
                    return Feedback::getFeedback(803, (array)$item);
                }

                $approvedWithChanges++;
                $daysApprovedWithChanges = $this->increaseItemOfArray($countOfDays, $daysApprovedWithChanges);

                if (in_array($item->predecessor, [1, 2, 3, 4])) {
                    $reasonCodes[$item->predecessor - 1] += $item->volume;

                } else {
                    return Feedback::getFeedback(804, (array)$item);
                }

            }
        }

        $daysRejected = $this->fillArrayByZeroDays($maxDay, $daysRejected);
        $daysApprovedWithChanges = $this->fillArrayByZeroDays($maxDay, $daysApprovedWithChanges);
        $daysApprovedWithoutChanges = $this->fillArrayByZeroDays($maxDay, $daysApprovedWithoutChanges);


        return Feedback::getFeedback(0, [
            'items' => [
                'count' => [
                    'rejected' => $rejected,
                    'approvedWithChanges' => $approvedWithChanges,
                    'approvedWithoutChanges' => $approvedWithoutChanges,
                ],
                'changes' => [
                    'code_1' => $reasonCodes[0],
                    'code_2' => $reasonCodes[1],
                    'code_3' => $reasonCodes[2],
                    'code_4' => $reasonCodes[3],
                ],
                'days' => [
                    'labels' => array_keys($daysRejected),
                    'rejected' => array_values($daysRejected),
                    'approvedWithChanges' => array_values($daysApprovedWithChanges),
                    'approvedWithoutChanges' => array_values($daysApprovedWithoutChanges),
                ],
            ]
        ]);
    }

    private function increaseItemOfArray($count, $array)
    {
        if ($count >= 0) {

            if (isset($array[$count])) {
                $array[$count] = $array[$count] + 1;

            } else {
                $array[$count] = (int)1;
            }
        }

        return $array;

    }

    private function fillArrayByZeroDays($max, $array)
    {
        $newArray = [];

        if (count($array) > 0) {
            for ($i = 0; $i <= $max; $i++) {
                $newArray[$i] = (isset($array[$i])) ? $array[$i] : 0;
            }
        }

        return $newArray;
    }

    private function getBacklogTimeForItemInTitleHistory($titleId)
    {
        $item = DB::table('titles_history')
            ->where('title_id', $titleId)
            ->where('status', 'BACKLOG')
            ->latest()
            ->first();

        return ($item === null) ? null : $item->created_at;

    }

    public function getItemsForCheckedDrawingsChart(Request $request)
    {
        $interval = intval(trim(Input::get('interval', '')));
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval((Input::get('date2', '')));
        $user_id = intval((Input::get('user_id', 0)));
        $file_reg_exp = trim(Input::get('file_regular_expression', ''));

        //DATE
        $startDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
        $endDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');

        $inItems = DB::table('checks')
            ->select('id', 'filename', 'status', 'mistake_count', 'owner', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('owner', $user_id)
            ->get()
            ->toArray();


        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($inItems as $key => $value) {
            if (preg_match($file_reg_exp, $value->filename) != 1) {
                unset ($inItems[$key]);
            }
        }

        $outItems = DB::table('checks')
            ->select('id', 'filename', 'status', 'mistake_count', 'owner', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->toArray();


        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($outItems as $key => $value) {
            if (preg_match($file_reg_exp, $value->filename) != 1) {
                unset ($outItems[$key]);
            }
        }

        // Ищем только записи, в которые проверены файлы user_id
        $userFileList = [];
        foreach ($outItems as $key => $value) {
            if ($value->owner == $user_id) { // если запись принадлежит user_id

                // это запись на проверку и ее нет в листе, добавляем в лист
                if ($value->status == 0 && !array_key_exists($value->filename, $userFileList)) {
                    $userFileList[] = $value->filename;
                }

                unset ($outItems[$key]);

            } else { // если запись не принадлежит user_id

                // если такого файла нет в листе, удаляем
                // если есть и другой пользователь добавил тот же файл на проверку, удаляем
                // в остальных случаях - оставляем
                if (array_key_exists($value->filename, $userFileList)) {
                    if ($value->status == 0) {
                        unset ($userFileList[$value->filename]);
                        unset ($outItems[$key]);
                    }
                } else {
                    unset ($outItems[$key]);
                }


            }
        }


        return Feedback::getFeedback(0, [
            'items' => [

                "in" => [
                    "drawings" => $this->divideItemsByIntervalUsingCount($inItems, $startDate, $endDate, $interval),
                    "mistakes" => $this->divideItemsByIntervalUsingValue($inItems, $startDate, $endDate, $interval, 'mistake_count')
                ],

                "out" => [
                    "drawings" => $this->divideItemsByIntervalUsingCount($outItems, $startDate, $endDate, $interval),
                    "mistakes" => $this->divideItemsByIntervalUsingValue($outItems, $startDate, $endDate, $interval, 'mistake_count')
                ],

            ]


        ]);
    }


}
