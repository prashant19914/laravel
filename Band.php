<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    protected $fillable = array(
        "name",
        "user_id",
        "website",
        "youtube",
        "facebook",
        "instagram",
        "twitter",
     );

    public function members()
    {
        return $this->hasMany('App\Member');
    }

    public function contact()
    {
        return $this->hasMany('App\Contact')->where('contacts.isband', '=','1');

    }

    public function searchContact($band_id)
    {   $result= array();
        $searchContacts=Contact::select()->where('isband','=','0')->where('band_id','=',$band_id)->get();
        foreach($searchContacts as $searchContact){
            $result[] = $searchContact->fname." ".$searchContact->lname;
        }
        return $result;
    }
}
