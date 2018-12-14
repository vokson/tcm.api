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

//        return var_dump($items);

        // Удаляем все титулы, не подходящие под регулярное выражение.
        foreach ($items as $key => $value) {
            if (preg_match($reg_exp, $value->title) != 1) {
                unset ($items[$key]);
            }

        }

        $s = [];
        $s[] = "START_DATE = " . $startDate;
        $s[] = "END_DATE = " . $endDate;
        $s[] = "INTERVAL = " . $interval;

        $countOfIntervals = intdiv(intval($endDate) - intval($startDate), $interval);
        $s[] = "COUNT OF INTERVALS = " . $countOfIntervals;
        $count = 0;
        $i = 0;
        $labels = [];
        $values = [];

        for ($n = 1; $n <= $countOfIntervals; $n++) {

            if ($i < count($items)) {
                $s[] = "COUNT=" . count($items) . "  I=" . $i . "  ITEM = " . $items[$i]->created_at;
            }

            while (
                ($i < count($items)) &&
                ($items[$i]->created_at < (intval($startDate) + $n * $interval))
            ) {
                $count++;
                $i++;
            }

            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = $count;
            $s[] = "DATE = " . (intval($startDate) + ($n - 1) * $interval) . "   COUNT = " . $count;
            $count = 0;

        }

        // n выходит из цикла увеличенным на 1

        $s[] = "N = " . $n;

        if ((intval($startDate) + ($n - 1) * $interval) < intval($endDate)) {
            $s[] = "INSIDE";
            $labels[] = intval($startDate) + ($n - 1) * $interval;
            $values[] = count($items) - $i;
            $s[] = "DATE = " . (intval($startDate) + ($n - 1) * $interval) . "   COUNT = " . (count($items) - $i);
        }

        $s[] = "ARRAY_SUM = " . array_sum($values);

//        return $s;

        $arr['labels'] = $labels;
        $arr['values'] = $values;

        return Feedback::getFeedback(0, [
            'items' => $arr
        ]);

    }


}
