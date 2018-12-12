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

        $date = intval($startDate);
        $count = 0;
        $chartItems = [];
        foreach ($items as $item) {
            $s[] = "ITEM = " . $item->created_at;
            if ($item->created_at >= ($date + $interval)) {
                $chartItems[$date] = $count;
                $s[] = "DATE = " . $date . "   COUNT = " . $count;
                $count = 0;
                $date += $interval;
            } else {
                $count++;
            }
        }

        $s[] = "EXIT";
        $s[] = "COUNT = " . $count;
        $s[] = "SUM OF CHART ITEMS = " . array_sum($chartItems);
        $s[] = "TOTAL COUNT = " . count($items);


        // Если эл-ты закончились, но дата не достигла endDate
        if ($date < intval($endDate)) {
            $chartItems[$date] = 0;
        }

        // Если эл-ты закончились и дата = endDate, ничего делать не нужно. Все четко сошлось.

        // Если эл-ты закончились, но дата больше, чем endDate
        if ($date > intval($endDate)) {
            $chartItems[$date] = $count;
        }

        $s[] = "SUM OF CHART ITEMS = " . array_sum($chartItems);

        return $s;

        if (array_sum($chartItems) <> count($items)) {
            return "DATE = " . $date . "END_DATE=" . intval($endDate) . "  " . array_sum($chartItems) . "<>" . count($items);
        }



        return Feedback::getFeedback(0, [
            'items' => $chartItems
        ]);

    }


}
