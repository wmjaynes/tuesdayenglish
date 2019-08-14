<?php

namespace App;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function array_shift;
use function count;
use function env;
use function explode;
use function file_get_contents;
use function json_decode;
use function now;
use function sizeof;
use function strlen;
use function strpos;
use function trim;
use function var_dump;

class DatabaseHelper
{
    public function reloadDatabase()
    {
        DB::table('caller_dance')->truncate();
        DB::table('callers')->truncate();
        DB::table('dances')->truncate();

        $this->loadCallers();
        $this->loadDances();
        $this->addDanceAttributes();

        DB::table('application')
            ->updateOrInsert(
                ['id' => 1],
                ['last_reloaded_at' => Carbon::now('+00:00')]);
    }

    public function addDanceAttributes()
    {
        $apiKey = env('SHEETS_API_KEY');
        $sheet_dances = env('DANCES');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheet_dances . '/values/Dances?key=' . $apiKey;
        $payload = json_decode(file_get_contents($url));

        array_shift($payload->values);
        foreach ($payload->values as $row) {

            if (count($row) == 0) {
                break;
            }
            $dance = Dance::where('name', 'LIKE', "{$row[5]}%")->first();

//            if ($dance == null) {
//                Log::debug('Dance not found: '.$row[5]);
//            }

            if ($dance != null) {
                $dance->meter = $row[7];
                $dance->key = $row[10];
                $dance->formation = $row[11];

                if (strpos($row[14], 'B') !== false) {
                    $dance->barnes = $row[14];
                }
                $dance->save();
            }
        }
    }

    public function test()
    {
        $apiKey = env('SHEETS_API_KEY');
        $most_recent = env('MOST_RECENT_DANCES_DONE');
        $url = 'https://www.googleapis.com/drive/v2/files/' .
            $most_recent . '/?key=' . $apiKey;
        $payload = json_decode(file_get_contents($url));

        $dateStr = $payload->modifiedDate;
        $carbDate = new Carbon($dateStr);
        $now = Carbon::now();
        dump($carbDate->toIso8601ZuluString());
        dump($carbDate->addMinutes(10)->toIso8601ZuluString());
        dump($now->toIso8601ZuluString());
        echo $now->toIso8601ZuluString();
        var_dump( $carbDate->greaterThan($now));
    }

    protected function loadCallers()
    {
        $apiKey = env('SHEETS_API_KEY');
        $sheet_18_19 = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheet_18_19 . '/values/Leaders?key=' . $apiKey;
        $payload = json_decode(file_get_contents($url));

        array_shift($payload->values);
        foreach ($payload->values as $row) {
            Caller::create(['code' => $row[0], 'name' => $row[1]]);
//            echo($row[0] . ' ' . $row[1].'\r');
//            var_dump($row);
        }
    }

    protected function loadDances()
    {
        $apiKey = env('SHEETS_API_KEY');

        $sheetId = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;
        $this->readFromSheet($url, 18);

        $sheetId = env('DANCES_DONE_17_18');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;
        $this->readFromSheet($url, 17);

    }

    protected function readFromSheet($url, $startYear)
    {
        $payload = json_decode(file_get_contents($url));

        array_shift($payload->values);
        $total = 0;
        foreach ($payload->values as $row) {
//            print "::".print_r($row, true)."\n";
            if (count($row) == 0) {
                break;
            }
            $dance = Dance::firstOrCreate(['name' => $row[0]]);

            if (sizeof($row) == 1)
                continue;
            if (ctype_space($row[1] || $row[1] == ''))
                continue;
            $dc = explode(', ', $row[1]);
            if (strlen(trim($dc[0])) == 0)
                continue;

            foreach ($dc as $item) {
                list($dt, $caller_code) = explode(' ', $item);
                list($month, $day) = explode('/', $dt);
                if ($month > 8)
                    $year = $startYear;
                else
                    $year = $startYear + 1;
                $date_of = new Carbon($dt . '/' . $year);

                $caller = Caller::where('code', $caller_code)->first();
                $dance->addCaller($caller, $date_of);

                $total++;
//                print $total."\n";
            }
        }
    }
}
