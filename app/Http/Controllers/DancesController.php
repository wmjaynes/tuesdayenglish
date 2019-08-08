<?php

namespace App\Http\Controllers;

use App\Dance;
use App\DatabaseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use PhpParser\Builder;
use function array_key_exists;
use function array_push;
use function dump;
use function explode;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Array_;
use function print_r;
use function response;
use function today;
use function var_dump;

class DancesController extends Controller
{

    public function dance()
    {
        $date = Carbon::today()->addMonths(-2);
//        Event::listen('illuminate.query', function($query)
//        {
//            Log::debug(print_r($query, true));
//        });
//        \DB::listen(function($sql) {
//            var_dump($sql);
//        });

        \DB::enableQueryLog();

        $dancesHasCallers = Dance::whereHas('callers', function ($query) {
            $query->where('date_of', '>', '2019-08-02');
        })->with(['callers' => function ($query)  {
            $query->where('date_of', '>', '2019-02-02');
        }])->get();

        dd(\DB::getQueryLog());


        $exists = "select * from callers inner join caller_dance on callers.id = caller_dance.caller_id where dances.id = caller_dance.dance_id and caller_dance.date_of > '2019-07-01)'";

        $dancesHasCallers = Dance::whereExists(function ($query) {
            $date = Carbon::today()->addMonths(-2);
            $query->select(DB::raw(1))
                ->from('callers')
                ->join('caller_dance', 'callers.id', '=', 'caller_dance.caller_id')
                ->where('dances.id', '=', 'caller_dance.dance_id')
                ->whereDate('caller_dance.date_of', '>', $date);
        })
            ->get();
        dump(\DB::getQueryLog());

        return response()->json($dancesHasCallers);

        $dance->callers;
        return response()->json($dance);
    }

    public function dances(Request $request)
    {
        $danceMonths = $request->input('danceRange', 3);
        $danceDate = Carbon::today()->addMonths(-$danceMonths);
        $historyMonths = $request->input('historyRange', 12);
        $historyDate = Carbon::today()->addMonths(-$historyMonths);

        Log::debug($danceDate->toDateString());

        $dances = Dance::whereHas('callers', function ($query) use ($danceDate) {
            $query->where('date_of', '>', $danceDate);
        })->with(['callers' => function ($query) use ($historyDate) {
            $query->where('date_of', '>', $historyDate);
        }])->get();

        return response()->json($dances);
    }

    public function dancesByDate(Request $request)
    {
        $date = Carbon::today()->addMonths(-12);
        $query = DB::table('dances')
            ->join('caller_dance', 'caller_dance.dance_id', '=', 'dances.id')
            ->join('callers', 'caller_dance.caller_id', '=', 'callers.id')
            ->whereDate('caller_dance.date_of', '>', $date)
            ->orderBy('caller_dance.date_of', 'desc')
            ->select('dances.name as dance_name', 'caller_dance.date_of',
                'callers.name as caller_name', 'dances.id as dance_id');

        $sql = $query->toSql();
//        Log::debug($sql);
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

        // Get number of times dance has been called in the time frame
        $query = DB::table('dances')
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

        $dancesHasCallers = Dance::has('callers')->with('callers')->get();
        foreach ($dancesHasCallers as $dance) {
            $dance->displayHistory = false;
            $dances[$dance->name] = $dance;
        }

        return response()->json(['danceCount' => $dance_count, 'byDate' => $byDate, 'dances' => $dances]);
    }

    public function reloadDatabase()
    {
        $helper = new DatabaseHelper();
        $helper->reloadDatabase();

        return response()->redirectTo('home');
    }
}
