<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Development extends Model
{
    // protected $table = 'xz_development';
    
    static function addType($name)
    {
        try{
            $res = DB::table('development')->insert(
                [
                    'name'=>$name,
                ]
            );
            return $res;
        }catch (\Exception $exception){
            $json['errcode']=500;
            $json['errmsg']=$exception->getMessage();
            return json_encode($json);
        }
    }
}
