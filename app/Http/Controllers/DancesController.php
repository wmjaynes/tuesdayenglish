<?php

namespace App\Http\Controllers;

use App\DatabaseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use function array_key_exists;
use function array_push;
use function explode;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Array_;
use function response;
use function today;
use function var_dump;

class DancesController extends Controller
{
    public function dancesByDate(Request $request)
    {
        $date = Carbon::today()->addMonths(-12);
        $query =  DB::table('dances')
            ->join('caller_dance', 'caller_dance.dance_id', '=', 'dances.id')
            ->join('callers', 'caller_dance.caller_id', '=', 'callers.id')
            ->whereDate('caller_dance.date_of', '>', $date)
            ->orderBy('caller_dance.date_of', 'desc')
            ->select('dances.name as dance_name', 'caller_dance.date_of', 'callers.name as caller_name');

        $sql = $query->toSql();
        Log::debug($sql);
        $dances_callers = $query->get();

        // Group dance/callers by date
        $byDate = array();
        foreach ($dances_callers as $dc) {
            $currentDate = $dc->date_of;
            if (!array_key_exists($currentDate, $byDate)) {
                $byDate[$currentDate] = array();
            }
            array_push($byDate[$currentDate], $dc);
        }

        $query =  DB::table('dances')
            ->join('caller_dance', 'caller_dance.dance_id', '=', 'dances.id')
            ->join('callers', 'caller_dance.caller_id', '=', 'callers.id')
            ->whereDate('caller_dance.date_of', '>', $date)
            ->groupBy('dances.name')
            ->select(DB::raw('dances.name as dance_name, count(*) as cnt'));
        $dance_cnt = $query->get();

        $dance_count = array();
        foreach ($dance_cnt as $dc) {
            $dance_count[$dc->dance_name] = $dc->cnt;
        }

        return response()->json(['danceCount' => $dance_count, 'byDate' => $byDate]);
    }

    public function reloadDatabase()
    {
        $helper = new DatabaseHelper();
        $helper->reloadDatabase();

        return response()->redirectTo('home');
    }
}
