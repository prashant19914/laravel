<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = array(
        "band_id",
        "mobile",
        "name",
        "website",
        "facebook",
        "vanue_id",
        "twitter",
        "street",
        "city",
        "state",
        "postcode",
        "country",
        "notes",
    );
    public function Vanue()
    {
        return $this->belongsTo('App\Vanue');
    }

}
