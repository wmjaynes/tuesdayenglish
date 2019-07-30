<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dance extends Model
{
    protected $dateFormat = 'Y-m-d';
    protected $fillable = ['name'];

    public function callers() {
        return $this->belongsToMany('App\Caller')->withPivot('date_of');;
    }

    public function addCaller($caller, $date)
    {
        $this->callers()->attach($caller, ['date_of'=>$date]);
    }

}
