<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pusher extends Model
{
    static function addPusher($userinfo)
    {
        try{
            $createtime = date('Y-m-d H:i:s');
            $res = DB::table('pusher')->insert(
                [
                    'name' => $userinfo['name'],
                    'phone' => $userinfo['phone'],
                    'groupid' => $userinfo['groupid'],
                    'createtime' => $createtime,
                    'updatetime' => $createtime,
                ]
            );
            return $res;
        }catch (\Exception $exception){
            $json['errcode']=500;
            $json['errmsg']=$exception->getMessage();
            return json_encode($json);
        }
    }
    static function selectNamePusher($name)
    {
        try{
            $createtime = date('Y-m-d H:i:s');
            $res = DB::table('manageinfo')->insert(
                [
                    'name' => $userinfo['name'],
                    'phone' => $userinfo['phone'],
                    'groupid' => $userinfo['groupid'],
                    'createtime' => $createtime,
                    'updatetime' => $createtime,
                ]
            );
            return $res;
        }catch (\Exception $exception){
            $json['errcode']=500;
            $json['errmsg']=$exception->getMessage();
            return json_encode($json);
        }
    }
    /**
	 * 除该小组外的所有成员
	 * @return [type] [description]
	 */
	static function selectUserName($groupid)
	{
		return json_decode(DB::table('pusher')->select('id','name','phone')->where('groupid','<>',$groupid)->get(),true);
    }
     /**
	 * 小组成员添加  
	 * @return [type] [description]
	 */
	static function groupUserAdd($addUserId, $currrentGroupId)
	{
        return $alltemp = DB::table('pusher')->where('id','=',$addUserId)->update([
            'groupid' => $currrentGroupId,
            'updatetime' => date('Y-m-d H:i:s'),
        ]);
    }
    /**
	 * 根据小组查询成员
	 * @return [type] [description]
	 */
	static function selectPusher($groupid)
	{
		return json_decode(DB::table('pusher')->select('id','name','phone')->where('groupid','=',$groupid)->get(),true);
    }
    /**
	 * 根据名字查询成员
	 * @return [type] [description]
	 */
	static function byNamePusher($name)
	{
		return json_decode(DB::table('pusher')->where('name','=',$name)->get(),true);
    }
    /**
	 * 根据手机号查询成员
	 * @return [type] [description]
	 */
	static function byPhonePusher($phone)
	{
		return json_decode(DB::table('pusher')->where('phone','=',$phone)->get(),true);
    }
     /**
	 * 成员添加
	 */
	static function addUser($userName,$userPhone,$groupId)
	{
        $adddata = [
            'name' => $userName,
            'phone' => $userPhone,
            'groupid' => $groupId,
            'createtime' => date('Y-m-d H:i:s'),
            'updatetime' => date('Y-m-d H:i:s'),
        ];
        DB::table('pusher')->Insert($adddata);
	}
}
