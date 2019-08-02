<?php

use App\DatabaseHelper;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $helper = new DatabaseHelper();
        $helper->reloadDatabase();
    }

}
