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
use function preg_replace;
use function sizeof;
use function strlen;
use function strpos;
use function strtolower;
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
        $this->loadDancesAndAttributes();
        $this->loadDances();

        DB::table('application')
            ->updateOrInsert(
                ['id' => 1],
                ['last_reloaded_at' => Carbon::now('+00:00')]);
    }

    public function loadDancesAndAttributes()
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
//            $danceNamePattern = preg_replace('/[^a-zA-Z]+/', '%', $row[5]);
//            $dance = Dance::where('name', 'LIKE', "{$danceNamePattern}%")->first();

            $dance = Dance::create(['name' => trim($row[5])]);


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
        $sheet_18_19 = env('DANCES_DONE_19_20');
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

        $sheetId = env('DANCES_DONE_19_20');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;
        $this->readFromSheet($url, 19);

        $sheetId = env('DANCES_DONE_18_19');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;
        $this->readFromSheet($url, 18);

        $sheetId = env('DANCES_DONE_17_18');
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;
        $this->readFromSheet($url, 17);

    }

    private $convertName = array(
        '1 is One and All Alone' => 'One Is One and All Alone',
        '4 for the Gospelmakers' => 'Four for the Gospelmakers',
        '5 for the Symbol at Your Door' => 'Five for the Symbol at Your Door',
        '6 for the Six Proud Walkers' => 'Six for the Six Proud Walkers',
        '7 for the Seven Stars in the Sky' => 'Seven for the Seven Stars in the Sky',
        '8 for the Music Makers' => 'Eight for the Music Makers',
        '9 for the Nine Bright Shiners' => 'Nine for the Nine Bright Shiners',
        '10 for the Ten Commandments' => 'Ten for the Ten Commandments',
        '12 for the Twelve Apostles' => 'Twelve for the Twelve Apostles',
        'Maid\'s Morris' => 'Maids Morris',
        'Haymakers, The / Highland Lilt' => 'Haymakers, The',
        'Anna Maria / Maria Anna, The' => 'Anna Maria',
        'Bemused Benthologist ' => 'Bemused Benthologist, The ',
        'Bellamira (Bolton)' => 'Bellamira',
        'Blackheath (Kalia Kliban recon.)' => 'Blackheath',
        'Chestnut / Dove\'s Figary' => 'Chestnut',
        'Come, Let\'s Be Merry' => 'Come Let\'s Be Merry',
        'De\'il Take the Warr' => 'De\'il Take the Wars',
        'Draper\'s Gardens (Shaw)' => 'Drapers Gardens',
        'Draper\'s Gardens' => 'Drapers Gardens',
        'Dublin Bay / We\'ll Wed and We\'ll Bed' => 'Dublin Bay',
        'Duke of Kent\'s Waltz, The (v I & II)' => 'Duke of Kent\'s Waltz, I, The',
        'Fit\'s Come on Me Now, The / Bishop of Chester\'s Jigg, The' => 'Bishop of Chester\'s Jigg',
        'Irfona\'s Waltz' => 'Irfôna\'s Waltz',
        'Windsor Wedges' => 'Windsor Wenches',
        'Up with Aily' => 'Up with Aily (3/2)',
        'Bemused Benthologist' => 'Bemused Benthologist, The',
        'Knole Park (v. I & II)' => 'Knole Park',
        'Rakes of Rochester, The (v. I & II)' => 'Rakes of Rochester, The',
        'Softly, Good Tummas' => 'Softly Good Tummas',
        'Treasure of the Big Woods, The' => 'Treasure of the Big Woods',
        'Trip to Tunbridge, A (v I & II)' => 'Trip to Tunbridge, A',
        'Hen Run' => 'Hen Run, The',
        'Hey, Boys, Up Go We / Cuckolds all in a Row' => 'Hey, Boys, Up Go We',
        'Jockey, The / Fourpence Ha\'penny Farthing' => 'Fourpence Ha\'penny Farthing',
        'King of Poland, The' => 'King of Poland',
        'Long Odds' => 'Long Odds, The',
        'Midnight Ramble' => 'Midnight Ramble, The',
        'Molly Andrew, The' => ' MollyAndrew, The',
        'Mrs. Pomeroy\'s Pavane / Bridgewater\'s Gain' => 'Mrs. Pomeroy\'s Pavane',
        'News from Tripoli' => 'News from Tripoly',
        'Old Hob / Mouse Trap, The' => 'Old Hob or the Mouse Trap',
        'Old Mill, The / Merry Salopians, The' => 'Merry Salopians, The',
        'Quite Carr-ied Away / Joan Transported' => 'Quite Carr-ied Away',

    );

    protected function readFromSheet($url, $startYear)
    {
        $payload = json_decode(file_get_contents($url));

        array_shift($payload->values);
        $total = 0;
        foreach ($payload->values as $row) {
//            Log::debug( "::".print_r($row, true));
            if (sizeof($row) == 0) {
                break;
            }
            if (sizeof($row) == 1)
                continue;
            if ($row[2] == 0)
                continue;

            // At this point we know the dance has been called at least once
            // in the spreadsheet, although it may not yet be in the database

            $row[0] = trim($row[0]);
            if (isset($this->convertName[$row[0]])) {
                $danceName = $this->convertName[$row[0]];
//                Log::debug("$danceName : $startYear :: danceName converted");
            }
            else {
                $danceName = $row[0];
            }

//            $dance = Dance::where('name', 'ilike', $danceName)->get();
            $dance = Dance::whereRaw('LOWER(name) = ?', strtolower($danceName))->get();
            if ($dance->isEmpty()) {
                Log::debug("$danceName : $startYear :: dance not found");
//                continue;
            }

            $dance = Dance::firstOrCreate(['name' => $danceName]);

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
