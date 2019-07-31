<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dance extends Model
{
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d';
    protected $fillable = ['name'];

    public function callers() {
        return $this->belongsToMany('App\Caller')->withPivot('date_of')
            ->orderBy('date_of','desc');
    }

    public function addCaller($caller, $date)
    {
        $this->callers()->attach($caller, ['date_of'=>$date]);
    }

}
