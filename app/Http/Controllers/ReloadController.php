<?php

namespace App\Http\Controllers;

use App\DatabaseHelper;
use Carbon\Carbon;
use function env;
use function file_get_contents;
use function json_decode;
use function response;

class ReloadController extends Controller
{


    public function reloadDatabase()
    {
        $helper = new DatabaseHelper();
        $helper->reloadDatabase();

        return response()->redirectTo('home');
    }

    public function doUpdateQuery()
    {
        $doUpdate = false;

        $apiKey = env('SHEETS_API_KEY');
        $most_recent = env('MOST_RECENT_DANCES_DONE');
        $url = 'https://www.googleapis.com/drive/v2/files/' .
            $most_recent . '/?key=' . $apiKey;

        $payload = json_decode(file_get_contents($url));
        $dateStr = $payload->modifiedDate;
        $lastModified = new Carbon($dateStr);

        $lastReloadedAtStr = \DB::table('application')->where('id', 1)->first()->last_reloaded_at;
        $lastReloadedAt = new Carbon($lastReloadedAtStr);
        $doUpdate = $lastModified->greaterThan($lastReloadedAt->addMinutes(0));

        return response()->json($doUpdate);
    }

}
