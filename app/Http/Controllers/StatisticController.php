<?php

namespace App\Http\Controllers;

use App\Status;
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
            'items' => $this->divideItemsByIntervalUsingSize($items, $startDate, $endDate, $interval)
        ]);
    }

    private function divideItemsByIntervalUsingSize($items, $startDate, $endDate, $interval)
    {

        $items = array_values($items);
        $countOfIntervals = intdiv(intval($endDate) - intval($startDate), $interval);

        $size = 0;
        $i = 0;

        $labels = [];
        $values = [];

        for ($n = 1; $n <= $countOfIntervals; $n++) {

            while (
                ($i < count($items)) &&
                ($items[$i]->created_at < (intval($startDate) + $n * $interval))
            ) {
                $size += $items[$i]->size;
                $i++;
            }

            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = $size;
            $size = 0;

        }

        // n выходит из цикла увеличенным на 1

        if ((intval($startDate) + ($n - 1) * $interval) < intval($endDate)) {
            $labels[] = intval($startDate) + ($n - 1) * $interval;

            $size = 0;
            for ($k = $i; $k < count($items); $k++) {
                $size += $items[$k]->size;
            }

            $values[] = $size;
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
            if (preg_match($title_reg_exp, $value->name) != 1 || preg_match($description_reg_exp, $value->description) != 1 ) {
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


}
