<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Groups extends Model
{
    static function addGroup($name)
    {
        $createtime = date('Y-m-d H:i:s');
        try{
            $res = DB::table('groups')->insert(
                [
                    'name'=>$name,
                    'updatetime'=>$createtime,
                    'createtime'=>$createtime,
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
	 * 修改名称
	 * @return [type] [description]
	 */
	static function selectgroupName($togroupname)
	{
        return json_decode(DB::table('groups')->where('name','=',$togroupname)->get(),true);
    }
    /**
	 * 修改名称
	 * @return [type] [description]
	 */
    static function groupNameEdit($groupid,$togroupname)
    {
        return json_decode(DB::table('groups')->where('groupid','=',$groupid)->update([
            'name' => $togroupname,
            'updatetime' => date('Y-m-d H:i:s'),
        ]),true);
    }
    /**
	 * 删除小组
	 * @return [type] [description]
	 */
    static function groupdelete($groupid)
    {
        return json_decode(DB::table('groups')->where('groupid','=',$groupid)->delete(),true);
    }
    /**
	 * 组选项
	 * @return [type] [description]
	 */
	static function allGroup()
	{
		$options = json_decode(DB::table('groups')->select('groupid','name')->get(),true);
		$nunname['groupid'] = '0';
		$nunname['name'] = '无分组';
		$options[] = $nunname;
		return $options;
	}
}
