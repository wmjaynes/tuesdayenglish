<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});
Route::get('/dancesHistory', function () {
    return view('dancesHistory');
})->name('dancesHistory');
Route::get('/callersHistory', function () {
    return view('callersHistory');
})->name('callersHistory');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dances', 'DancesController@dances')->name('dances');
Route::get('/program', 'DancesController@program')->name('program');

Route::get('/dancesbydate', 'DancesController@dancesByDate');

Route::get('/reload', 'ReloadController@reloadDatabase')->name('reload');
Route::get('/doupdatequery', 'ReloadController@doUpdateQuery')->name('doupdatequery');

Route::get('/test/', function () {
    return view('test');
});

Route::get('/callers', 'CallersController@dances')->name('dances');
