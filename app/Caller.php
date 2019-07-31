<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caller extends Model
{
    public $timestamps = false;
    protected $fillable = ['code', 'name'];

    public function dances()
    {
        return $this->belongsToMany('App\Dance')->withPivot('date_of');
    }

    public function addDance($dance, $date)
    {
        $this->dances()->attach($dance, ['date_of'=>$date]);
    }
}
