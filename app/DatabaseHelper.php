<?php

namespace App;


use function array_shift;
use Carbon\Carbon;
use function count;
use function env;
use function explode;
use function file_get_contents;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use function json_decode;
use function sizeof;
use function strlen;
use function trim;

class DatabaseHelper
{
    public function reloadDatabase()
    {
        DB::table('caller_dance')->truncate();
        DB::table('callers')->truncate();
        DB::table('dances')->truncate();

        $this->getCallers();
        $this->getDances();
    }

    protected function getCallers() {
        $apiKey = env('SHEETS_API_KEY');
        $sheet_18_19 = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/'.
            $sheet_18_19.'/values/Leaders?key='.$apiKey;
        $payload = json_decode(file_get_contents($url));

        array_shift( $payload->values);
        foreach ($payload->values as $row) {
            Caller::create(['code' => $row[0], 'name' => $row[1] ]);
//            echo($row[0] . ' ' . $row[1].'\r');
//            var_dump($row);
        }
    }

    protected function getDances()
    {
        $apiKey = env('SHEETS_API_KEY');

        $sheetId = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/'.
            $sheetId.'/values/Master?key='.$apiKey;
        $this->readFromSheet($url, 18);

        $sheetId = env('DANCES_DONE_17_18');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/'.
            $sheetId.'/values/Master?key='.$apiKey;
        $this->readFromSheet($url, 17);

    }

    protected function readFromSheet($url, $startYear)
    {
        $payload = json_decode(file_get_contents($url));

        array_shift( $payload->values);
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
