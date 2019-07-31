<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Caller;
use App\Dance;
use App\DanceParty;
use function Psy\debug;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->loadCallers();
        $this->loadDances();
    }

    protected function loadCallers() {
        $apiKey = env('SHEETS_API_KEY');
        $sheet_18_19 = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/'.
            $sheet_18_19.'/values/Leaders?key='.$apiKey;
        $payload = json_decode(file_get_contents($url));

        array_shift( $payload->values);
        foreach ($payload->values as $row) {
            Caller::create(['code' => $row[0], 'name' => $row[1] ]);
        }
    }

    protected function loadDances()
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
