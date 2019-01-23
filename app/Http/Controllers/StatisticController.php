<?php

namespace App\Http\Controllers;

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




}
