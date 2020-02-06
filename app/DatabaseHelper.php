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

        $this->readFromSheet($apiKey, 'DANCES_DONE_19_20',19);
        $this->readFromSheet($apiKey, 'DANCES_DONE_18_19',18);
        $this->readFromSheet($apiKey, 'DANCES_DONE_17_18',17);
        $this->readFromSheet($apiKey, 'DANCES_DONE_16_17',16);
        $this->readFromSheet($apiKey, 'DANCES_DONE_15_16',15);
        $this->readFromSheet($apiKey, 'DANCES_DONE_14_15',14);
        $this->readFromSheet($apiKey, 'DANCES_DONE_13_14',13);
        $this->readFromSheet($apiKey, 'DANCES_DONE_12_13',12);
        $this->readFromSheet($apiKey, 'DANCES_DONE_11_12',11);
        $this->readFromSheet($apiKey, 'DANCES_DONE_10_11',10);
        $this->readFromSheet($apiKey, 'DANCES_DONE_09_10',9);

    }



    protected function readFromSheet($apiKey, $fileKey, $startYear)
    {
        $sheetId = env($fileKey);
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/' .
            $sheetId . '/values/Master?key=' . $apiKey;

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

//            $dance = Dance::whereRaw('LOWER(name) = ?', strtolower($danceName))->get();
//            if ($dance->isEmpty()) {
//                Log::debug("$danceName : $startYear :: dance not found");
//            }

            $dance = Dance::where('name', $danceName)->first();
            if ($dance == null) {
                Log::debug("$startYear : '$danceName' => '', :: dance not found");
                $dance = Dance::create(['name' => $danceName]);
            }

//            $dance = Dance::firstOrCreate(['name' => $danceName]);

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

    private $convertName = array(
        '1 is One and All Alone' => 'One Is One and All Alone',
        '10 for the Ten Commandments' => 'Ten for the Ten Commandments',
        '12 for the Twelve Apostles' => 'Twelve for the Twelve Apostles',
        '4 for the Gospelmakers' => 'Four for the Gospelmakers',
        '5 for the Symbol at Your Door' => 'Five for the Symbols at Your Door',
        'Five for the Symbol at Your Door' => 'Five for the Symbols at Your Door',
        '5 for the Symbols at Your Door' => 'Five for the Symbol at Your Door',
        '6 for the Six Proud Walkers' => 'Six for the Six Proud Walkers',
        '7 for the Seven Stars in the Sky' => 'Seven for the Seven Stars in the Sky',
        '8 for the Music Makers' => 'Eight for the Music Makers',
        '9 for the Nine Bright Shiners' => 'Nine for the Nine Bright Shiners',
        'Aberdeen / De\'ils Dead, The' => 'Aberdeen or the De\'il\'s Dead',
        'All Saint\'s Day' => 'All Saints\'',
        'American Husband, The / Her Man' => 'American Husband, The',
        'Anna Maria / Maria Anna, The' => 'Anna Maria',
        'Barham Down (v I & II)' => 'Barham Down',
        'Bell of the Ball' => 'Belle of the Ball',
        'Bellamira (Bolton)' => 'Bellamira',
        'Belle of Greenboro, The' => 'Belle of Greensboro',
        'Belle of Greensboro, The' => 'Belle of Greensboro',
        'Belle of Northampton' => 'Belle of Northampton, The',
        'Bemused Benthologist ' => 'Bemused Benthologist, The ',
        'Bemused Benthologist' => 'Bemused Benthologist, The',
        'Black Nag, The' => 'Black Nag',
        'Blackheath (Kalia Kliban recon.)' => 'Blackheath',
        'Blayden Races' => 'Blaydon Races',
        'Cadger\'s Caper' => 'Cadgers\' Caper',
        'Cadger\'s Other Caper' => 'Cadgers\' Other Caper',
        'Chestnut / Dove\'s Figary' => 'Chestnut',
        'Chocolate Equation' => 'Chocolate Equation, The',
        'Chocolate Round-O, The' => 'Chocolate Round O, The',
        'Cholocate for Breakfast' => 'Chocolate for Breakfast',
        'Chrysallis' => 'Chrysalis',
        'Columbus Anniversary / John Shaw\'s Maggot' => 'Columbus Anniversary',
        'Come, Let\'s Be Merry' => 'Come Let\'s Be Merry',
        'Comical Fellow , The' => 'Comical Fellow, The',
        'Cream Pot, The' => 'Cream Pot',
        'Dance in the Village, The' => 'Dance in the Village',
        'Dancing Across the Atlantic' => 'Dancing across the Atlantic',
        'Danske Delight' => 'Danske Delights',
        'Dargason / Sedany' => 'Dargason',
        'De\'il Take The Warr' => 'De\'il Take the Wars',
        'Dr. Bending\'s Serpent' => 'Doctor Bending\'s Serpent',
        'Dr. Who' => 'Doctor Who',
        'Draper\'s Gardens (Shaw)' => 'Drapers Gardens',
        'Draper\'s Gardens' => 'Drapers Gardens',
        'Dublin Bay / We\'ll Wed and We\'ll Bed' => 'Dublin Bay',
        'Duke of Kent\'s Waltz, The (v I & II)' => 'Duke of Kent\'s Waltz, I, The',
        'Fast Packet' => 'Fast Packet, The',
        'Fete Champetre' => 'Fête Champêtre',
        'Fit\'s Come on Me Now, The / Bishop of Chester\'s Jigg, The' => 'Bishop of Chester\'s Jigg',
        'Halfway Round the World' => 'Halfway \'round the World',
        'Handel With Care' => 'Handel with Care',
        'Haymakers, The / Highland Lilt' => 'Haymakers, The',
        'Hen Run' => 'Hen Run, The',
        'Hessian, The' => 'Hessian Dance, The',
        'Hey, Boys, Up Go We / Cuckolds all in a Row' => 'Hey, Boys, Up Go We',
        'I Care Not For These Ladies' => 'I Care Not for These Ladies',
        'Irfona\'s Waltz' => 'Irfôna\'s Waltz',
        'Jockey, The / Fourpence Ha\'penny Farthing' => 'Fourpence Ha\'penny Farthing',
        'John Tallis\' Canon' => 'John Tallis\'s Canon',
        'Jovial Beggars, The (Bolton)' => 'Jovial Beggars, The (Playford)',
        'Jumper\'s Chase' => 'Jumpers Chase',
        'K & E' => 'K and E',
        'Kill Him With Kindness' => 'Kill Him with Kindness',
        'King of Poland, The' => 'King of Poland',
        'Knole Park (v. I & II)' => 'Knole Park',
        'Lady Williams\' Delight' => 'Lady Williams\'s Delight',
        'Lancaster Lasses' => 'Lancaster Lasses, The',
        'Le Debauche' => 'Le Débauché',
        'Leather Lake House' => 'Leather Lake House (TML)',
        'Leaving of Liverpool' => 'Leaving of Liverpool, The',
        'Levi Jackson Ragg, The' => 'Levi Jackson Rag',
        'Long Odds' => 'Long Odds, The',
        'Mad Robin, The' => 'Mad Robin',
        'Maid Peeped Out at the Window / Friar in the Well, The' => 'Maid Peeped Out at the Window',
        'Maid\'s Morris' => 'Maids Morris',
        'Manches Vertes, Les' => 'Les Manches Vertes',
        'MC2' => 'MC²',
        'Merrily We Dance and Sing / Fiilip, The' => 'Merrilly We Dance and Sing',
        'Merry Conclusion, The / Mr. Kynaston\'s Famous Dan' => 'Merry Conclusion, The',
        'Midnight Ramble' => 'Midnight Ramble, The',
        'Miss De Jersey\'s Memorial' => 'Miss de Jersey\'s Memorial',
        'Molly Andrew, The' => 'MollyAndrew, The',
        'Mover and Shaker , A' => 'Mover and Shaker, A',
        'Mr Millstone\'s Inauguration' => 'Mr. Millstone\'s Inauguration',
        'Mrs. Pomeroy\'s Pavane / Bridgewater\'s Gain' => 'Mrs. Pomeroy\'s Pavane',
        'New Beginning , A' => 'New Beginning, A',
        'News From Tripoli' => 'News from Tripoly',
        'Nightwatch' => 'Night Watch',
        'Nonesuch / A la Mode de France' => 'Nonesuch',
        'Nonesuch Two' => 'Nonesuch II',
        'Now is the Month of Maying' => 'Now Is the Month of Maying',
        'Old Batchelor' => 'Old Bachelor',
        'Old Hob / Mouse Trap, The' => 'Old Hob, or the Mouse Trap',
        'Old Mill, The / Merry Salopians, The' => 'Merry Salopians, The',
        'Old Wife Behind the Fire' => 'Old Wife behind the Fire',
        'Parson Upon Dorothy' => 'Parson upon Dorothy',
        'Pilgrim, The / Lord Foppington' => 'Pilgrim, The',
        'Pleasures of the Town, The / Three Meet' => 'Pleasures of the Town, The',
        'Prof. Kekule\'s Reverie' => 'Professor Kekule\'s Reverie',
        'Promise of Spring, The' => 'Promise of Spring',
        'Put on Thy Smock on a Monday' => 'Put On Thy Smock on a Monday',
        'Queens Borow' => 'Queen\'s Borrow',
        'Quite Carr-ied Away / Joan Transported' => 'Quite Carr-ied Away',
        'Rakes of Rochester, The (v. I & II)' => 'Rakes of Rochester, The',
        'Rose of Sharon, The' => 'Rose of Sharon',
        'Round About Our Coal Fire' => 'Round about Our Coal Fire',
        'Row Well Ye Mariners' => 'Row Well, Ye Mariners',
        'Saint Katherine' => 'Saint Catherine',
        'Salutation, The' => 'Salutation',
        'Sellenger\'s Round / Beginning of the World, The' => 'Sellenger\'s Round',
        'Short And Sweet' => 'Short and Sweet',
        'Short and the Tall, The / Shooting Stars' => 'Short and the Tall, The',
        'Softly, Good Tummas' => 'Softly Good Tummas',
        'South Wind' => 'Southwind (Winston)',
        'Southwind' => 'Southwind (Winston)',
        'Sunlight Through Draperies' => 'Sunlight through Draperies',
        'Swirl of the Sea' => 'Swirl of the Sea, The',
        'Treasure of the Big Woods, The' => 'Treasure of the Big Woods',
        'Trip O\'er Tweed, A' => 'Trip o\'er Tweed, A',
        'Trip to Bury' => 'Trip to Bury, A',
        'Trip to Tunbridge, A (v I & II)' => 'Trip to Tunbridge, A',
        'Two Cousins' => 'Two Cousins, The',
        'Unknown Buccaneer, The' => 'Unknown Buccaneer',
        'Up With Aily' => 'Up with Aily (3/2)',
        'Upon A Summer\'s Day' => 'Upon a Summer\'s Day',
        'Waltham Abbey' => 'Waltham Abbey (Roodman)',
        'Waters of Holland' => 'Waters of Holland, The',
        'Wa\' is Me, What Mun I Do' => 'Wa\' Is Me, What Mun I Do!',
        'Whiskey Before Dinner' => 'Whiskey before Dinner',
        'White Cockade, The' => 'White Cockade',
        'Windsor Wedges' => 'Windsor Wenches',
        'Winter Dreams Waltz' => 'Winter Dreams',
        'Wive\'s Victory, The' => 'Wives\' Victory, The',
        'Wood Lark, The' => 'Wood Lark',
        'Wright\'s of Lichfield, The' => 'Wrights of Lichfield, The',
        'Zither Man' => 'Zither Man, The',
        'Halfe Hannikan' => 'Halfe Hannikin',
        'Haymakers, The  / Highland Lilt' => 'Haymakers, The',
        'Heidenroslein' => 'Heidenröslein',
        'Joy After Sorrow' => 'Joy after Sorrow',
        'Peace Be With You' => 'Peace Be with You',
        'Merry Conclusion, The / Mr. Kynaston\'s Famous Dance' => 'Merry Conclusion, The',
        'Maid Peeped Out At The Window / Friar in the Well, The' => 'Maid Peeped Out at the Window',
        'Kingsail' => 'Kingsale',
        'Jovial Beggars, The' => 'Jovial Beggars, The (Playford)',
        'Cobbler\'s Hornpipe / Mr. Englefield\'s New Hornpipe' => 'Cobbler\'s Hornpipe',
        'Dunsmuir Waltz' => 'Dunsmuir Waltz, The',
        'Forward and Back / F and B' => 'Forward and Back',
        'Greenwich Park (v I & II)' => 'Greenwich Park (Playford)',
    );
}
