<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Development;
use App\Models\Manageinfo;
use App\Models\Typeinfo;
use App\Models\Groups;
use App\Models\Pusher;
use App\Models\Landuser;
use App\Http\Controllers\WxinfoController;
use App\Libs\SendMsgService;

class LandpushController extends Controller
{
    public function addDevelopment()
    {
        $credentials = request();
        $res = Development::addType($credentials['name']);
        return response()->json($res);
    }
    public function addManageinfo()
    {
        $credentials = request();
        $res = Manageinfo::addType($credentials['name']);
        return response()->json($res);
    }
    public function addTypeinfo()
    {
        $credentials = request();
        $res = Typeinfo::addType($credentials['name']);
        return response()->json($res);
    }
    //添加用户
    public function addPusher(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:pusher',
            'phone' => 'required|unique:pusher',
        ]);
        $credentials = request();
        $res = Pusher::addPusher($credentials);
        return response()->json($res);
    }
    /**
	 * 添加分组
	 */
	public function addGroup(Request $request)
	{	
        $validatedData = $request->validate([
            'name' => 'required|unique:groups',
        ]);
		$credentials = request();
        $res = Groups::addGroup($credentials['name']);
        return response()->json($res);
    }
    /**
	 * 查询组
	 * @return [type] [description]
	 */
	public function allGroup()
	{
		//有成员的小组
		$temp = DB::table('pusher')->leftJoin('groups', 'pusher.groupid', '=', 'groups.groupid')
		->select('groups.groupid','groups.name','groups.createtime',DB::raw('count(*) as usercounts'))
		->groupBy('groups.groupid')
        ->get();
        
		//成员总数 
        $userCount = DB::table('pusher')->count();
        //在小组内的成员总数
        $inGroupUserCount = DB::table('pusher')->where("groupid", '<>', 0)->count();
        $groupuser = [];
        $temp = json_decode($temp,true);
		foreach ($temp as $key => $value) {
			$groupuser[$key] = $value['groupid'];
		}
        $allgroup = DB::table('groups')->select('groupid','name','createtime')->orderBy('createtime', 'DESC')->get();
        $allgroup = json_decode($allgroup,true);
		$groupCount = count($allgroup); 
		foreach ($allgroup as $key => $value) {
			if (in_array($value['groupid'],$groupuser)) {
				foreach ($temp as $k => $v) {
					if ($v['groupid'] == $value['groupid']) {
						$allgroup[$key]['usercounts'] = $v['usercounts'];
					}
				}
			}else{
				$allgroup[$key]['usercounts'] = 0;
			}
        }
        // return response()->json($allgroup);
		$count = [
			'userCount' => $userCount,//成员总数
			'inGroupUserCount' => $inGroupUserCount,//在小组内的成员总数
			'groupCount' => $groupCount,//小组数
		];
		$data['count'] = $count;
		$data['grouplist'] = $allgroup;
		$code = 0;
        $message = '请求成功';
        $result = [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
		return dataBack($code,$message,$data);
    }
    /**
	 * 小组搜索
	 * @return [type] [description]
	 */
	public function groupSearch(Request $request)
	{
		$info = request();
		if (isset($info['search']) && ($info['search'] || $info['search'] === "0")) {
			$search = $info['search'];
			$temp = DB::table('pusher')->leftJoin('groups', 'pusher.groupid', '=', 'groups.groupid')
			->select('groups.groupid','groups.name',DB::raw('count(*) as usercounts'),'groups.createtime')
			->groupBy('groups.groupid')
			->where('groups.name', 'like', '%'.$search.'%')
            ->get();
            $temp = json_decode($temp,true);
            $alltemp = DB::table('groups')->select('groupid','name','createtime')->where('name', 'like', '%'.$search.'%')->groupBy('groupid')->orderBy('createtime', 'desc')->get();
            $alltemp = json_decode($alltemp,true);
			$groupCount = count($alltemp); 
            $groupuser = [];
            if (!empty($temp)) {
                foreach ($temp as $key => $value) {
                    $groupuser[$key] = $value['groupid'];
                }
            }
			foreach ($alltemp as $key => $value) {
				if (in_array($value['groupid'],$groupuser)) {
					foreach ($temp as $k => $v) {
						if ($v['groupid'] == $value['groupid']) {
							$alltemp[$key]['usercounts'] = $v['usercounts'];
						}
					}
				}else{
					$alltemp[$key]['usercounts'] = 0;
				}
            }
            $usercounts = 0;
			foreach ($alltemp as $key => $value) {
				$usercounts += $value['usercounts'];
			}
			$count = [
				'userCount' => $usercounts,//成员总数
				'inGroupUserCount' => $usercounts,//在小组内的成员总数
				'groupCount' => $groupCount,//小组数
			];
			$data['count'] = $count;
			$data['grouplist'] = $alltemp;
			$code = 0;
			$message = '搜索成功';
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
    }
    /**
	 * 小组详情
	 * @return [type] [description]
	 */
	public function groupDetails()
	{
		$info = request();
		if (isset($info['groupid']) && !empty($info['groupid'])) {
			$groupid = $info['groupid'];
			$map['groupid'] = $info['groupid'];
			$groupinfo =DB::table('pusher')->leftJoin('groups', 'pusher.groupid', '=', 'groups.groupid')
            ->select('groups.groupid','groups.name as groupname',DB::raw('count(*) as usercounts'),'groups.createtime as groupcreatetime')
            ->groupBy('groups.groupid')
			->where('groups.groupid','=',$groupid)
			->get();
			$userinfo =  DB::table('pusher')
			->select('id','name as username','phone','createtime')
			->where('groupid','=',$groupid)
            ->get();
			$data['groupinfo'] = $groupinfo;
			$data['userinfo'] = $userinfo;
			$code = 0;
			$message = '请求成功';
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
    }
    /**
	 * 小组成员搜索
	 * @return [type] [description]
	 */
	public function groupUserSearch()
	{
		$info = request();
		if (!empty($info['searchGroupId']) && ($info['searchName'] || $info['searchName'] === "0")) {
			$searchGroupId = $info['searchGroupId'];
			$search = $info['searchName'];
            $alltemp = DB::table('pusher')
            ->select('groupid','name as username','phone','createtime')
            // ->where('name', 'like', '%'.$search.'%')
            ->where([
                ['name', 'like','%'.$search.'%'],
                ['groupid', '=', $searchGroupId],
            ])
            ->orWhere([
                ['phone', '=',$search],
                ['groupid', '=', $searchGroupId],
            ])
            ->orderBy('createtime','desc')
            ->get();
			$data['searchInfo'] = $alltemp;
			$code = 0;
			$message = '搜索成功';
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
    }
    /**
	 * 小组成员移除   //一个成员只能在一个小组   若选无分组传0
	 * @return [type] [description]
	 */
	public function groupUserRemove()
	{
		$info = request();
		if (isset($info['removeGroupId']) && (!empty($info['removeUserId']) && (!empty($info['addGroupId'] || $info['addGroupId'] === '0')))) {
			$removeGroupId = $info['removeGroupId'];
			$removeUserId = $info['removeUserId'];
			$addGroupId = $info['addGroupId'];
			if ($removeGroupId == $addGroupId) {
				$code = -1111;
				$message = '移动小组和原小组相同';
			}else{
				if (!empty($removeUserId)) {
					$searchtemp = DB::table('pusher')->where([
                        ['groupid','=',$removeGroupId],
                        ['id','=',$removeUserId],
                    ])->get();
					if (empty($searchtemp)) {
						$code = -1112;
						$message = '原组信息不存在';
					}else{
						$alltemp = DB::table('pusher')->where([
                            ['groupid','=',$removeGroupId],
                            ['id','=',$removeUserId],
                        ])->update([
                            'groupid' => $addGroupId,
                            'updatetime' => date('Y-m-d H:i:s'),
                        ]);
						$code = 0;
						$message = '移动成功';
					}
				}else{
					$code = -1113;
					$message = '参数为空';
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data=[]);
    }
    /**
	 * 小组成员添加  
	 * @return [type] [description]
	 */
	public function groupUserAdd(Request $request)
	{
		$info = request();
		if ((!empty($info['currrentGroupId']) || $info['currrentGroupId'] === "0") && !empty($info['addUserId'])) {
			$currrentGroupId = $info['currrentGroupId'];//原小组
			$addUserId = $info['addUserId'];//成员id
			$alltemp = Pusher::groupUserAdd($addUserId, $currrentGroupId);
			if (empty($alltemp)) {
				$code = -2001;
				$message = '添加失败';
			}else{
				$code = 0;
				$message = '添加成功';
			}
		}else{
			$code = -1001;
			$message = '传参错误';
		}
		return dataBack($code,$message);
    }
    /**
	 * 除该小组外的所有成员
	 * @return [type] [description]
	 */
	public function selectUserName()
	{
		$info = request();
		if (!empty($info['groupId'])) {
			$searchtemp = Pusher::selectUserName($info['groupId']);
			$code = 0;
			$message = '查询成功';
			$data = $searchtemp;
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
    }
    /**
	 * 修改名称
	 * @return [type] [description]
	 */
	public function groupNameEdit(Request $request)
	{
		$info = request();
		if (!empty($info['groupId']) && !empty($info['groupName']) && !empty($info['togroupName'])) {
			if ($info['groupName'] == $info['togroupName']) {
				$code = -3001;
				$message = '新组名与原组名一致';
			}else{
                $res = Groups::selectgroupName($info['togroupName']);
				if (!empty($res)) {
					$code = -3002;
					$message = '组名已存在';
				}else{
					$alltemp = Groups::groupNameEdit($info['groupId'],$info['togroupName']);
					if (empty($alltemp)) {
						$code = -2001;
						$message = '修改失败';
						$data = [];
					}else{
						$code = 0;
						$message = '修改成功';
						$data = $alltemp;
					}
				}
				
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data=[]);
    }
    /**
	 * 删除小组
	 * @return [type] [description]
	 */
	public function deleteGroup()
	{
		$info = request();
		if (!empty($info['groupId'])) {
			$searchtemp = Pusher::selectPusher($info['groupId']);
			if (empty($searchtemp)) {
				Groups::groupdelete($info['groupId']);
				$code = 0;
				$message = '删除成功';
			}else{
				$code = -2001;
				$message = '不能删除';
			}
		}else{
			$code = -1001;
			$message = '传参错误';
		}
		return dataBack($code,$message,$data=[]);
    }
    /**
	 * 组选项
	 * @return [type] [description]
	 */
	public function allGroupOptions()
	{
		$options = Groups::allGroup();
		$code = 0;
		$message = '查询成功';
		return dataBack($code,$message,$options);
    }
    /**
	 * 成员添加
	 */
	public function addUser()
	{
		$info = request();
		if ((!empty($info['groupId']) || $info['groupId'] === "0") && !empty($info['userName'])) {
			$groupId = $info['groupId'];//原小组
			$userName = $info['userName'];//成员id
			$userPhone = $info['userPhone'];//成员id
			$alltemp = Pusher::byNamePusher($userName);
			if (!empty($alltemp)) {
				$code = -4001;
				$message = '成员名称已存在';
			}else{
				$phoneinfo = Pusher::byPhonePusher($userPhone);
				if (!empty($phoneinfo)) {
					$code = -4002;
					$message = '成员手机号已存在';
				}else{
					Pusher::addUser($userName,$userPhone,$groupId);
					$code = 0;
					$message='添加成功';
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
		}
		return dataBack($code,$message);
    }
    /******************************成员列表***********************************/
	/**
	 * 成员列表
	 * @return [type] [description]
	 */
	public function userList()
	{
		//无小组成员
        $nogroup = DB::table('pusher')->select('id','groupid','name as username','phone','status')->where('groupid','=',0)->get();
        $nogroup = json_decode($nogroup,true);
		foreach ($nogroup as $key => $value) {
			$nogroup[$key]['groupname'] = '暂无小组';
		}
		//有小组的成员
		$temp = DB::table('pusher')->leftJoin('groups','pusher.groupid','=', 'groups.groupid')
		->select('pusher.id','groups.groupid','groups.name as groupname','pusher.name as username','pusher.phone','pusher.status')
		->groupBy('pusher.id')
		->orderBy('pusher.name')
        ->get();
        $temp = json_decode($temp,true);
		$alltemp = array_merge($temp,$nogroup);
		$isgroup = count($temp);
		$alluser = count($alltemp);
		// $allgroup = $options = DB::table('groups')->select('groupid','name')->get();
		// $nunname['groupid'] = '0';
		// $nunname['name'] = '暂无分组';
		$allgroup = Groups::allGroup();
		$data['count'] = [
			"alluserCount" => $alluser,
			"groupuserCount" => $isgroup,
		];
		$data['group'] = $allgroup;
		$data['user'] = $alltemp;
		$code = 0;
		$message = '查询成功';
		return dataBack($code,$message,$data);
	}
	/**
	 * 跟据组查成员
	 * @return [type] [description]
	 */
	public function groupToUser()
	{
		$info = request();
		if (!empty($info['groupId']) || $info['groupId'] === "0") {
			if ($info['groupId'] == 0) {
				$map['groupid'] = $info['groupId'];
				$temp = DB::table('pusher')
				->select('id','groupid','name as username','phone','status')
				->where('groupid','=',$info['groupId'])
				->orderBy('pusher.name')
                ->get();
                $temp = json_decode($temp,true);
				foreach ($temp as $key => $value) {
					$temp[$key]['groupname'] = '暂无分组';
				}
			}else{
				$temp = DB::table('pusher')->leftJoin('groups','pusher.groupid', '=','groups.groupid')
				->select('pusher.id','groups.groupid','groups.name as groupname','pusher.name as username','pusher.phone','pusher.status')
				->groupBy('pusher.id')
				->where('pusher.groupid','=',$info['groupId'])
				->orderBy('pusher.name')
                ->get();
                $temp = json_decode($temp,true);
			}
			$code = 0;
			$message = '查询成功';
			$data = $temp;
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 跟据手机号或者姓名查成员 传 searchName或searchPhone
	 * @return [type] [description]
	 */
	public function searchUser()
	{
		$info = request();
		if ($info['searchName'] || $info['searchName'] === "0") {
			$search = $info['searchName'];
            $groupinfo = DB::table('pusher')->select('id','groupid','name as username','phone','status')->where('name','=',$search)->orWhere('phone','=',$search)->get();
            $groupinfo = json_decode($groupinfo,true);
			if (empty($groupinfo)) {
				$data = [];
			}else{
				if (isset($groupinfo[0]['groupid']) && $groupinfo[0]['groupid'] == 0) {
					$groupinfo[0]['groupname'] = '暂无分组';
					$data = $groupinfo;
				}else{
					$temp = DB::table('pusher')->leftJoin('groups','pusher.groupid', '=', 'groups.groupid')
					->select('pusher.id','groups.groupid','groups.name as groupname','pusher.name as username','pusher.phone','pusher.status')
					->groupBy('pusher.id')
                    ->where('pusher.name','=',$search)
                    ->orWhere('pusher.phone','=',$search)
					->orderBy('pusher.name')
                    ->get();
                    $temp = json_decode($temp,true);
					$data = $temp;
				}
			}
			$code = 0;
			$message = '搜索成功';
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
    }
    

    /**
	 * 地拖成员删除 传参 pusherStrId 为数组  删除成功会返回 noDeleteInfo 不能删除的人员信息，以及已经删除的 isDeleteInfo 人员信息，如果没有可以删除的，则isDeleteInfo为空
	 * @return [type] [description]
	 */
	public function pusherDelete()
	{
		$info = request();
		if (!empty($info['pusherStrId'])) {
			$pusherStrId = $info['pusherStrId']; 
            $belongid = DB::table('landuser')->select('belongid','viewuser')->get();
			$belongid = json_decode($belongid,true);
					
			foreach ($belongid as $key => $value) {
				$belong[$key] = $value['belongid'];
				$viewuser[$key] = json_decode($value['viewuser'],true);
			}
			foreach ($viewuser as $key => $value) {
				foreach ($value as $k => $v) {
					if (!in_array($v,$belong)) {
						$belong[] = $v;
					}
					
				}
			}
			foreach ($pusherStrId as $key => $value) {
				if (in_array($value,$belong)) {
					$nodelet[] = json_decode($value,true);
				}else{
					$isdelet[] = json_decode($value,true);
				}
			}
			if (empty($nodelet)) {
				$noDeleteInfo = [];
			}else{
                $noDeleteInfo = DB::table('pusher')->select('id','groupid','name','phone','status')->whereIn('id',$nodelet)->get();
                $noDeleteInfo = json_decode($noDeleteInfo,true);
			}
			if (empty($isdelet)) {
				$isDeleteInfo = [];
			}else{
                $isDeleteInfo = DB::table('pusher')->whereIn('id',$isdelet)->get();
                $isDeleteInfo = json_decode($isDeleteInfo,true);
				$deleteStatus = DB::table('pusher')->whereIn('id',$isdelet)->delete();
			}
			$data['noDeleteInfo'] = $noDeleteInfo;
			$data['isDeleteInfo'] = $isDeleteInfo;
			$code = 0;
			$message = '删除成功';
			
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];		
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 改变成员状态    传参 pusherStrId 为数组  status 0为启用  1为禁用
	 * @return [type] [description]
	 */
	public function editStatus()
	{
		$info = request();
		if (isset($info['pusherStrId']) && isset($info['status'])) {
			$pusherStrId = $info['pusherStrId']; 
			$status = $info['status'];
			$data = DB::table('pusher')->whereIn('id',$pusherStrId)->update([
                'status' => $status,
                'updatetime' => date('Y-m-d H:i:s'),
            ]);
			$code = 0;
			$message = '更改成功';
			
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];		
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 成员分组 参数: groupId pusherStrId(数组)    返回值successCount 成功数  failCount失败数
	 * @return [type] [description]
	 */
	public function userGroup()
	{
		$info = request();
		if (!empty($info['pusherStrId']) && (!empty($info['groupId']) || $info['groupId'] === "0")) {
			$pusherStrId = $info['pusherStrId']; 
			$groupId = $info['groupId'];
			$countall = count($pusherStrId);
			$successCount = json_decode(DB::table('pusher')->whereIn('id',$pusherStrId)->update([
                'groupid' => $groupId,
                'updatetime' => date('Y-m-d H:i:s'),
            ]),true);
			$data['successCount'] = $successCount;
			$failCount = $countall - $successCount;
			$data['failCount'] = $failCount;
			$code = 0;
			$message = '分组成功';
			
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];		
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 修改地推者信息 pusherId 地推人员id pusherName 地推人员要修改成的姓名 pusherPhone 地推人员要修改成的手机号
	 * @return [type] [description]
	 */
	public function editPusher()
	{
		$info = request();
		if (!empty($info['pusherId']) && (!empty($info['pusherName']) && !empty($info['pusherPhone']))) {
			$pusherId = $info['pusherId'];
			$pusherName = $info['pusherName'];
			$pusherPhone = $info['pusherPhone'];
			$createtime = date('Y-m-d H:i:s');
            $res = DB::table('pusher')->where('id','=',$pusherId)->get();
			$res = json_decode($res,true);
			if ($res[0]['name'] == $pusherName && $res[0]['phone'] == $pusherPhone) {
				$code = -3001;
				$message = '没有改变名称和手机号';
				$data = [];
			}else if ($res[0]['name'] != $pusherName && $res[0]['phone'] != $pusherPhone) {
                $resname = Pusher::byNamePusher($pusherName);
				if (!empty($resname)) {
					$code = -3002;
					$message = '名称已存在';
					$data = [];
				}else{
                    $phoneres = Pusher::byPhonePusher($pusherPhone);
					if (!empty($phoneres)) {
						$code = -3003;
						$message = '手机号已存在';
						$data = [];
					}else{
						$code = 0;
						$message = "手机号和名称更新成功";
						$data = json_decode(DB::table('pusher')->where('id','=',$pusherId)->update([
                            'name' => $pusherName,
                            'phone' => $pusherPhone,
                            'updatetime' => date('Y-m-d H:i:s'),
                        ]),true);
					}
				}
			}else if ($res[0]['name'] != $pusherName) {
                $resname = Pusher::byNamePusher($pusherName);
				if (!empty($resname)) {
					$code = -3002;
					$message = '名称已存在';
					$data = [];
				}else{
					$data = json_decode(DB::table('pusher')->where('id','=',$pusherId)->update([
                        'name' => $pusherName,
                        'updatetime' => date('Y-m-d H:i:s'),
                    ]),true);
					$code = 1;
					$message = "名称更新成功";
				}
				
			}else{
				$phoneres = Pusher::byPhonePusher($pusherPhone);
				if (!empty($phoneres)) {
					$code = -3003;
					$message = '手机号已存在';
					$data = [];
				}else{
					$data = json_decode(DB::table('pusher')->where('id','=',$pusherId)->update([
                        'phone' => $pusherPhone,
                        'updatetime' => date('Y-m-d H:i:s'),
                    ]),true);
					$code = 2;
					$message = "手机号更新成功";
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];		
		}
		return dataBack($code,$message,$data);
    }
    
    /**************************************数据统计***********************************************/
	/**
	 * 系统管理中的数据统计  今日和七天选参数 days   自定义时传 endTime  和  days (可选)若有传，若没有可不传，若传传时间戳 秒为单位:1526951517       定义actionType 1 为全部  2为搜索 actionType为2时传userName    比如一天:从今天的00:00 到后一天的00:00    两点:1.时间内添加的地推人员 2.时间内上报的 
	 *  上传数为
	 * @return [type] [description]
	 */
	public function systemData()
	{
		$info = request();
		if (!empty($info['actionType'])) {
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];

			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
			//上报总数(加时间条件)
			// $allmap['createtime'] = array('between',array($startTime,$endTime));
            $allCount = DB::table('landuser')
                ->select(DB::raw('count(*) as allCount'))
                ->whereBetween('createtime',[$startTime,$endTime])
                ->get();
			$allCount = json_decode($allCount,true);
			// $share['groupid'] = array('NEQ',0);
			// $share['createtime'] = array('between',array($startTime,$endTime));
            $shareCount = DB::table('landuser')
                ->select(DB::raw('count(*) as sharecount'))
                ->where('groupid','NEQ',0)
                ->whereBetween('createtime',[$startTime,$endTime])
                ->get();
            $shareCount = json_decode($shareCount,true);
			$data['count']['allCount'] = $allCount[0]['allCount'];
			$data['count']['shareCount'] = $shareCount[0]['sharecount'];
			$data['count']['days'] = $days;
            $groupname = DB::table('groups')->select('groupid','name')->get();
            $groupname = json_decode($groupname,true);
			foreach ($groupname as $key => $value) {
				$grouparr[$value['groupid']] = $value['name'];
			}
			if ($info['actionType'] == "1") {
				//时间内添加的地推人员
				// $map['pusher.createtime'] = array('ELT',$endTime);
				// $map['landuser.createtime'] = array('between',array($startTime,$endTime));
				// $map['landuser.groupid'] = array('NEQ',0);
				$sharetemp = DB::table('pusher')->leftJoin('landuser','pusher.id','=','landuser.belongid')
				->join('groups','pusher.groupid', '=','groups.groupid')
				->select('pusher.id as userid','pusher.name as userName','groups.name as groupName',DB::raw('count(*) as shareCount'))
                ->where('landuser.groupid','<>',0)
				->whereBetween('landuser.createtime',[$startTime,$endTime])
				->groupBy('pusher.id')
				->orderBy('pusher.name')
                ->get();
				$sharetemp = json_decode($sharetemp,true);
				
				// // $no['landuser.groupid'] = 0;
				// // $no['landuser.createtime'] = array('between',array($startTime,$endTime));
				$alltemp = DB::table('pusher')->leftJoin('landuser','pusher.id','=','landuser.belongid')
				->select('pusher.id as userid','pusher.name as userName','pusher.groupid',DB::raw('count(*) as userallCount'))
				->whereBetween('landuser.createtime',[$startTime,$endTime])
				->groupBy('pusher.id')
				->orderBy('pusher.name')
				->get();
				// $data = $alltemp;
				$alltemp = json_decode($alltemp,true);
				
				foreach ($sharetemp as $key => $value) {
					$userid[] = $value['userid'];
					$select[$value['userid']] = $value['shareCount'];
				}
				foreach ($alltemp as $key => $value) {
					if (in_array($value['userid'],$userid)) {
						$alltemp[$key]['shareCount'] = $select[$value['userid']];
					}else{
						$alltemp[$key]['shareCount'] = 0;
					}
					if ($value['groupid'] == 0) {
						$alltemp[$key]['groupname'] = "暂无小组";
					}else{
						$alltemp[$key]['groupname'] = $grouparr[$value['groupid']];
					}
				}
				$data['count']['aboutUserCount'] = count($alltemp);
				$data['userList'] = $alltemp;
				$code = 0;
				$message = "查询成功";
			}else{
				$searchname = $info['username'];
				// $no['landuser.groupid'] = 0;
				$no['landuser.createtime'] = array('between',array($startTime,$endTime));
				$no['pusher.name'] = $searchname;
				$alltemp = DB::table('pusher')->leftJoin('landuser','pusher.id', '=','landuser.belongid')
				->select('pusher.id as userId','pusher.name as userName',DB::raw('count(*) as userallCount'),'pusher.groupid')
                ->where('pusher.name','=',$searchname)
				->whereBetween('landuser.createtime',[$startTime,$endTime])
                ->groupBy('pusher.id')
                ->get();
                $alltemp = json_decode($alltemp,true);
				if (empty($alltemp)) {
					$data['userList'] = [];
				}else{
					// $map['landuser.createtime'] = array('between',array($startTime,$endTime));
					// $map['landuser.groupid'] = array('NEQ',0);
					// $map['pusher.name'] = $searchname;
					$sharetemp = DB::table('pusher')->leftJoin('landuser','pusher.id','=','landuser.belongid')
					->join('groups','pusher.groupid','=','groups.groupid')
					->select('pusher.id as userId','pusher.name as userName','groups.name as groupName',DB::raw('count(*) as shareCount'),'pusher.groupid')
                    ->where([
                        ['landuser.groupid','<>',0],
                        ['pusher.name','=',$searchname],
					])
					->whereBetween('landuser.createtime',[$startTime,$endTime])
                    ->groupBy('pusher.id')
                    ->get();
					$sharetemp = json_decode($sharetemp,true);
					$info = [];
					foreach ($sharetemp as $key => $value) {
						$info['sharecount'] = $value['shareCount'];
						$info['groupid'] = $value['groupid'];
					}
					if (empty($sharetemp)) {
						$alltemp[0]['shareCount'] = 0;
						$alltemp[0]['groupname'] = "暂无小组";
					}else{
						$alltemp[0]['shareCount'] = $info['sharecount'];
						$alltemp[0]['groupname'] = $grouparr[$info['groupid']];
					}
					$data['userList'] = $alltemp;
				}
				$data['count']['aboutUserCount'] = count($alltemp);
				$code = 0;
				$message = "查询成功";
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 上报详情 pusherId  days  endTime  days(天数, 今天days为1,7天为7,自定义时如从5.17到5.22 days为6)  
	 *		  endTime(只在自定义时间时传,截至时以s为单位的时间戳如:1526865117)    actionType  1 时表示全部  需要传
	 * @return [type] [description]
	 */
	public function reportDetails()
	{
		$info = request();
		if (!empty($info['actionType']) && !empty($info['pusherId'])) {
			$pusherId = $info['pusherId'];
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];
			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
            $typeinfo = DB::table('typeinfo')->get();
            $typeinfo = json_decode($typeinfo,true);
			foreach ($typeinfo as $key => $value) {
				$typearr[$value['id']] = $value['name'];
			}
            $manageinfo = DB::table('manageinfo')->get();
            $manageinfo = json_decode($manageinfo,true);
			foreach ($manageinfo as $key => $value) {
				$managearr[$value['id']] = $value['name'];
			}
            $developmentinfo = DB::table('development')->get();
            $developmentinfo = json_decode($developmentinfo,true);
			foreach ($developmentinfo as $key => $value) {
				$developmentarr[$value['id']] = $value['name'];
			}
            $groupname = DB::table('groups')->select('groupid','name')->get();
            $groupname = json_decode($groupname,true);
			foreach ($groupname as $key => $value) {
				$grouparr[$value['groupid']] = $value['name'];
			}
			$sexarr = [
				'男'=>0,
				'女'=>1
			];
			if ($info['actionType'] == 1) {
				// $no['landuser.createtime'] = array('between',array($startTime,$endTime));
				// $no['landuser.belongid'] = $pusherId;
				$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
				->select('pusher.id as userid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime')
                ->where('landuser.belongid','=',$pusherId)
				->whereBetween('landuser.createtime',[$startTime,$endTime])
				->orderBy('landuser.createtime','desc')
                ->get();
				$temp = json_decode($temp,true);
				// $data = $temp;
				$data = [];
				if (!empty($temp)) {
					foreach ($temp as $key => $value) {
						$temp[$key]['typename'] = $typearr[$value['typeid']];
						$temp[$key]['sexname'] = $temp[$key]['sex'];
						foreach (json_decode($value['manageid'],true) as $k => $v) {
							// $temp[$key]['managename'][$v] = $managearr[$v];
							$temp[$key]['manageInfo'][$k]['manageid'] = $v;
							$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
						}
						foreach (json_decode($value['development'],true) as $k => $v) {
							// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
							$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
							$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
						}
						if ($value['groupid'] == 0) {
							$temp[$key]['groupname'] = "暂无小组";
						}else{
							$temp[$key]['groupname'] = $grouparr[$value['groupid']];
						}
						$data[$key]['pusherid'] = $temp[$key]['userid'];
						$data[$key]['pushername'] = $temp[$key]['pushersname'];
						$data[$key]['groupid'] = $temp[$key]['groupid'];
						$data[$key]['groupname'] = $temp[$key]['groupname'];
						$data[$key]['landusername'] = $temp[$key]['landusername'];
						$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
						$data[$key]['sexname'] = $temp[$key]['sexname'];
						$data[$key]['typeid'] = $temp[$key]['typeid'];
						$data[$key]['typename'] = $temp[$key]['typename'];
						$data[$key]['landuserphone'] = $temp[$key]['phone'];
						$data[$key]['province'] = $temp[$key]['province'];
						$data[$key]['city'] = $temp[$key]['city'];
						$data[$key]['area'] = $temp[$key]['area'];
						$data[$key]['address'] = $temp[$key]['address'];
						$data[$key]['content'] = json_decode($temp[$key]['content'],true);
						$data[$key]['createtime'] = $temp[$key]['createtime'];
						$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
						$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
						
					}
				}else{
					$data = [];
				}
				$code = 0;
				$message = '成功';
			}else{
				if (isset($info['searchName'])) {
					$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as userid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime')
					->where([
                        ['landuser.belongid','=',$pusherId],
                        ['landuser.username','=',$info['searchName']],
					])
					->orWhere([
                        ['landuser.belongid','=',$pusherId],
                        ['landuser.phone','=',$info['searchName']],
					])
				    ->whereBetween('landuser.createtime',[$startTime,$endTime])
                    ->get();
                    $temp = json_decode($temp,true);
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							$temp[$key]['typename'] = $typearr[$value['typeid']];
							$temp[$key]['sexname'] = $temp[$key]['sex'];
							foreach (json_decode($value['manageid'],true) as $k => $v) {
								// $temp[$key]['managename'][$v] = $managearr[$v];
								$temp[$key]['manageInfo'][$k]['manageid'] = $v;
								$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
							}
							foreach (json_decode($value['development'],true) as $k => $v) {
								// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
								$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
								$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
							}
							if ($value['groupid'] == 0) {
								$temp[$key]['groupname'] = "暂无小组";
							}else{
								$temp[$key]['groupname'] = $grouparr[$value['groupid']];
							}
							$data[$key]['pusherid'] = $temp[$key]['userid'];
							$data[$key]['pushername'] = $temp[$key]['pushersname'];
							$data[$key]['groupid'] = $temp[$key]['groupid'];
							$data[$key]['groupname'] = $temp[$key]['groupname'];
							$data[$key]['landusername'] = $temp[$key]['landusername'];
							$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
							$data[$key]['sexname'] = $temp[$key]['sexname'];
							$data[$key]['typeid'] = $temp[$key]['typeid'];
							$data[$key]['typename'] = $temp[$key]['typename'];
							$data[$key]['landuserphone'] = $temp[$key]['phone'];
							$data[$key]['province'] = $temp[$key]['province'];
							$data[$key]['city'] = $temp[$key]['city'];
							$data[$key]['area'] = $temp[$key]['area'];
							$data[$key]['address'] = $temp[$key]['address'];
							$data[$key]['content'] = json_decode($temp[$key]['content'],true);
							$data[$key]['createtime'] = $temp[$key]['createtime'];
							$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
							$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}else{
					$code = -2001;
					$message = '请输入要搜索的名字';
					$data = [];	
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 经营品种和拓展成果所有类
	 * @return [type] [description]
	 */
	public function allManageDevelopment()
	{
		$data['manage'] = json_decode(DB::table('manageinfo')->get(),true);
		$data['development'] = json_decode(DB::table('development')->get(),true);
		$data['type'] = json_decode(DB::table('typeinfo')->get(),true);
		$code = 0;
		$message = '成功';
		return dataBack($code,$message,$data);
    }
	/**************************************************用户上报****************************************************************/
    /**
	 * 新增上报 pusherId 进来时接口给的 pusherId
	 * @return [type] [description]
	 */
	public function addReport()
	{
		$info = request();
		if (!empty($info['name']) && (!empty($info['phone']) && !empty($info['pusherId']))) {
			$pusherId = $info['pusherId'];
			$name = $info['name'];
			$phone = $info['phone'];
			// $phonemap['phone'] = $phone;
            $res = DB::table('landuser')->where('phone','=',$phone)->get();
            $res = json_decode($res,true);
			if (empty($res)) {
				$sex = $info['sexid'];
				$typeid = $info['typeid'];
				$manageid = $info['manageid'];
				$development = $info['developmentid'];
				$province = $info['province'];
				$city = $info['city'];
				$area = $info['area'];
				$address = $info['address'];
				// $groupmap['id'] = $pusherId;
                $temp = DB::table('pusher')->where('id','=',$pusherId)->get();
                $temp = json_decode($temp,true);
				$groupid = $temp[0]['groupid'];
				$belongid = $pusherId;
				if ($groupid == 0) {
					$viewuser = 0;
				}else{
					// $groupnom['groupid'] = $groupid;
                    $resgroup = DB::table('pusher')->select('id')->where('groupid','=',$groupid)->get();
                    $resgroup = json_decode($resgroup,true);
					foreach ($resgroup as $key => $value) {
						$view[] = $value['id'];
					}
					$viewuser = $view;
				}
				$addinfo = [
					'username' => $name,
					'phone' => $phone,
					'sex' => $sex,
					'typeid' => $typeid,
					'manageid' => json_encode($manageid),
					'development' => json_encode($development),
					'province' => $province,
					'city' => $city,
					'area' => $area,
					'address' => $address,
					'createtime' => date('Y-m-d H:i:s'),
					'updatetime' => date('Y-m-d H:i:s'),
					'belongid' => $belongid,
					'viewuser' => json_encode($viewuser),
					'groupid' => $groupid,
					'content' => '',
				];
				DB::table('landuser')->Insert($addinfo);
				$code = 0;
				$message = '上报成功';
			}else{
				$code = -3001;
				$message = '手机号已存在';
			}
		}else{
			$code = -1001;
			$message = '参数错误';
		}
		return dataBack($code,$message,$data=[]);
	}
	/**
	 * 手机号绑定
	*/
	public function phoneBind()
	{
		$info = request();
		if (!empty($info['openid']) && !empty($info['phone'])) {
			$openidtemp = DB::table('pusher')->where('openid','=',$info['openid'])->get();
			$openidtemp = json_decode($openidtemp,true);
			if (!empty($openidtemp)) {
				$code = -2001;
				$message = '该微信号已绑定';
				$data = [];
			}else{
				$map['phone'] = $info['phone'];
				$temp = DB::table('pusher')->where('phone','=',$info['phone'])->get();
				$temp = json_decode($temp,true);
				if (empty($temp)) {
					$code = -2002;
					$message = '没有该手机号';
					$data = [];
				}else{
					if (!empty($temp[0]['openid']) && $temp[0]['openid'] != $info['openid']) {
						$code = -2003;
						$message = '该手机号已绑定';
						$data = [];
					}else{
						// $createtime = date('Y-m-d H:i:s');
						// $saveinfo = [
						// 	'openid' => $info['openid'],
						// 	'updatetime' => $createtime,
						// ];
						$res = DB::table('pusher')->where('phone','=',$info['phone'])->update([
							'openid' => $info['openid'],
							'updatetime' => date('Y-m-d H:i:s'),
						]);
						$res = json_decode($res,true);
						$non['phone'] = $info['phone'];
						$tempids = DB::table('pusher')->where('phone','=',$info['phone'])->get();
						$tempids = json_decode($tempids,true);
						$data['pusherInfo']['pusherId'] = $tempids[0]['id'];
						$code = 0;
						$message = '手机号绑定成功';
					}
				}
			}
		}else{
			$code = -1001;
			$message = '参数错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 用户上报统计
	 * @return [type] [description]
	 */
	public function landuserReport()
	{
		$info = request();
		if (!empty($info['actionType']) && !empty($info['pusherId'])) {
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];

			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
			//上报总数(加时间条件)
			
			// $aboutno['landuser.createtime'] = array('between',array($startTime,$endTime));
			$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
			->select('landuser.userid as landuserid','pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
			->whereBetween('landuser.createtime',[$startTime,$endTime])
			->orderBy('landuser.createtime','desc')
			->get();
			$temp = json_decode($temp,true);
			$allAbout = [];
			$allgroup = [];
			foreach ($temp as $key => $value) {
				if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
					$allAbout[] = $temp[$key];
				}
				if (in_array($info['pusherId'],json_decode($value['viewuser'],true))) {
					$allShareAbout[] = $temp[$key];
					$ingroup = json_decode($value['viewuser'],true);
					foreach ($ingroup as $k => $v) {
						if (!in_array($v,$allgroup)) {
							$allgroup[] = $v;
						}
					}
				}
			}
			// $data[0] = $allAbout;
			// $data[1] = $allgroup;
			$res = [];
			foreach ($allAbout as $key => $value) {
				$res[$value['createtime']][$value['landuserid']] = $value;
				$datearr[] = $value['createtime'];
			}
			for ($i=0; $i < $days; $i++) { 
				$date = date("Y-m-d",strtotime("+".$i."day",$startSec));
				if (!in_array($date,$datearr)) {
					$countInfo[$i]['createtime'] = $date;
					$countInfo[$i]['counts'] = 0;
				}else{
					$countInfo[$i]['createtime'] = $date;
					$countInfo[$i]['counts'] = count($res[$date]);
				}
			}
			$nono['belongid'] = $info['pusherId'];
			$nono['groupid'] = 0;
			$nogroup = DB::table('landuser')->where($nono)->get();
			$nogroup = json_decode($nogroup,true);
			if (empty($nogroup)) {
				$othershe = 0;
			}else{
				$othershe = 1;
			}
			$allaboutUser = count($allgroup);
			$data['count']['allCount'] = count($allAbout);
			$data['count']['shareCount'] = count($allShareAbout) + $othershe;
			$data['count']['aboutUserCount'] = $allaboutUser;
			$data['count']['days'] = $days;
			$data['daysList'] = $countInfo;
			$code = 0;
			$message = "查询成功";
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 用户搜索 actionType 1 全范围搜索 2 时间和名字搜索(传传要搜索日期当天的时间戳和名字)  3 时间搜索(传要搜索日期当天的时间戳)
	 * @return [type] [description]
	 */
	public function landuserSearch()
	{
		$info = request();
		if (!empty($info['actionType']) && !empty($info['pusherId'])) {
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];
			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
			$typeinfo = DB::table('typeinfo')->get();
			$typeinfo = json_decode($typeinfo,true);
			foreach ($typeinfo as $key => $value) {
				$typearr[$value['id']] = $value['name'];
			}
			$manageinfo = DB::table('manageinfo')->get();
			$manageinfo = json_decode($manageinfo,true);
			foreach ($manageinfo as $key => $value) {
				$managearr[$value['id']] = $value['name'];
			}
			$developmentinfo = DB::table('development')->get();
			$developmentinfo = json_decode($developmentinfo,true);
			foreach ($developmentinfo as $key => $value) {
				$developmentarr[$value['id']] = $value['name'];
			}
			$groupname = DB::table('groups')->select('groupid','name')->get();
			$groupname = json_decode($groupname,true);
			foreach ($groupname as $key => $value) {
				$grouparr[$value['groupid']] = $value['name'];
			}
			$sexarr = [
				'男'=>0,
				'女'=>1
			];
			if ($info['actionType'] == 1) {
				if (!isset($info['search'])) {
					$code = -2001;
					$message = '请输入要搜索的手机号';
					$data = [];
				}else{
					$search = $info['search'];
					// $allno['landuser.phone'] = $search;
					$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as pusherid','pusher.name as pushersname','landuser.userid as landuserid','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					->where('landuser.phone','=',$search)
					->orderBy('landuser.createtime','desc')
					->get();
					$temp = json_decode($temp,true);
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								if ($value['groupid'] == 0) {
									$temp[$key]['groupname'] = "暂无小组";
								}else{
									$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								}
								$data[$key]['pusherid'] = $temp[$key]['pusherid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landuserid'] = $temp[$key]['landuserid'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
							
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}
			}else if($info['actionType'] == 2){
				if (isset($info['search']) && isset($info['currentSec'])) {
					$search = $info['search'];
					$currentDate = date('Y-m-d',$info['currentSec']);
					// $name['landuser.username'] = array('like',array('%'.$search.'%','%'.$search,$search.'%'),'OR');
					// $name['landuser.phone'] = $search;
					// $name['_logic'] = 'or';
					// $allno['_complex'] = $name;
					// $allno['landuser.createtime'] = $currentDate;
					$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as userid','pusher.name as pushersname','landuser.groupid','landuser.userid as landuserid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					// ->where($allno)
					->where([
						['landuser.username', 'like','%'.$search.'%'],
						['landuser.createtime', '=', $currentDate],
					])
					->orWhere([
						['landuser.phone', '=',$search],
						['landuser.createtime', '=', $currentDate],
					])
					->get();
					$temp = json_decode($temp,true);
					// $sql = DB::table('landuser')->getLastSql();
					// $data['sql'] = $sql;
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								if ($value['groupid'] == 0) {
									$temp[$key]['groupname'] = "暂无小组";
								}else{
									$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								}
								$data[$key]['pusherid'] = $temp[$key]['userid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landuserid'] = $temp[$key]['landuserid'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}else{
					$code = -2001;
					$message = '传入要搜索的时间戳或名字';
					$data = [];
					
				}
					
			}else{
				if (isset($info['currentSec'])) {
					$currentDate = date('Y-m-d',$info['currentSec']);
					// $allno['landuser.createtime'] = $currentDate;
					$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.userid as landuserid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					->where('landuser.createtime', '=', $currentDate)
					->get();
					$temp = json_decode($temp,true);
					if (!empty($temp)) {
						
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								if ($value['groupid'] == 0) {
									$temp[$key]['groupname'] = "暂无小组";
								}else{
									$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								}
								$data[$key]['pusherid'] = $temp[$key]['pusherid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landuserid'] = $temp[$key]['landuserid'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
							
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}else{
					$code = -2001;
					$message = '请输入要搜索的名字';
					$data = [];
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 修改上报信息
	 * @return [type] [description]
	 */
	public function editReport()
	{
		$info = request();
		if (!empty($info['name']) && (!empty($info['phone']) && !empty($info['landuserId']))) {
			$name = $info['name'];
			$phone = $info['phone'];
			$res = DB::table('landuser')
			->where([
				['phone', '=', $phone],
				['username', '=', $name],
				['userid', '=', $info['landuserId']],
			])
			->get();
			$res = json_decode($res,true);
			if (!empty($res)) {
				$typeid = $info['typeid'];
				$manageid = $info['manageid'];
				$development = $info['developmentid'];
				$province = $info['province'];
				$city = $info['city'];
				$area = $info['area'];
				$address = $info['address'];
				DB::table('landuser')->where('userid','=',$info['landuserId'])->update([
					'typeid' => $typeid,
					'manageid' => json_encode($manageid),
					'development' => json_encode($development),
					'province' => $province,
					'city' => $city,
					'area' => $area,
					'address' => $address,
					'updatetime' => date('Y-m-d H:i:s'),
				]);
				$code = 0;
				$message = '修改成功';
			}else{
				$code = -3001;
				$message = '手机号、名字不能修改';
			}
		}else{
			$code = -1001;
			$message = '参数错误';
		}
		return dataBack($code,$message);
	}
	/**
	 * 删除上报信息
	 * @return [type] [description]
	 */
	public function deleteReport()
	{
		$info = request();
		if (!empty($info['landuserStrId'])) {
			// $map['userid'] = array('in', $info['landuserStrId']);
			$data = DB::table('landuser')
			->whereIn('userid',$info['landuserStrId'])
			->delete();
			$code = 0;
			$message = '删除成功';
			
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];		
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 添加备注信息
	 */
	public function addContent()
	{
		$info = request();
		if (isset($info['content']) && (!empty($info['pusherId']) && !empty($info['landuserStrId']))) {
			$content = $info['content'];
			// $userno['id'] = $info['pusherId'];
			$res = DB::table('pusher')
			->where('id','=',$info['pusherId'])
			->get();
			$res = json_decode($res,true);
			$contentname = $res[0]['name'];
			// $map['userid'] = array('in', $info['landuserStrId']);

			$oldcontetn = DB::table('landuser')
			->whereIn('userid',$info['landuserStrId'])
			->get();
			$oldcontetn = json_decode($oldcontetn,true);
			foreach ($oldcontetn as $key => $value) {
				$oldcon[$value['userid']] = json_decode($value['content'],true);
			}
			$createtime = date('Y-m-d H:i:s');
			$contentarr['contentby'] = $contentname;
			$contentarr['createtime'] = $createtime;
			$contentarr['content'] = $content;
			foreach ($info['landuserStrId'] as $key => $value) {
				if (empty($oldcon[$value])) {
					$contentall[$key][] = $contentarr;
				}else{
					$contentall[$key] = $oldcon[$value];
					$contentall[$key][] = $contentarr;
				}
				// $savedata['content'] = json_encode($contentall[$key]);
				// $savedata['createtime'] = $createtime;
				// $namp['userid'] = $value;
				$data = json_decode(DB::table('landuser')->where('userid','=',$value)->update([
					'content' => json_encode($contentall[$key]),
					'createtime' => date('Y-m-d H:i:s'),
				]),true);
			}
			$code = 0;
			$message='备注成功';
		}else{
			$data = [];
			$code = -1001;
			$message='参数错误';
		}
		return dataBack($code,$message,$data);
	}



	/**
	 * 成果统计 allCount          (上报分享数)
	 *		  shareCount        (上报总数)
	 *		  myshareCount      (我的分享数)
	 *		  otherShareCount   (他人的分享数)
	 * @return [type] [description]
	 */
	public function achieveData()
	{
		$info = request();
		if (!empty($info['actionType']) && !empty($info['pusherId'])) {
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];

			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
			//上报总数(加时间条件)
			

			// $aboutno['landuser.createtime'] = array('between',array($startTime,$endTime));
			$temp = DB::table('landuser')->leftJoin('pusher','landuser.belongid','=','pusher.id')
			->select('landuser.userid as landuserid','pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
			->whereBetween('landuser.createtime',[$startTime,$endTime])
			->orderBy('landuser.createtime','desc')
			->get();
			$temp = json_decode($temp,true);
			$allAbout = [];
			$allgroup = [];
			foreach ($temp as $key => $value) {
				if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
					$allAbout[] = $temp[$key];
				}
				if (in_array($info['pusherId'],json_decode($value['viewuser'],true))) {
					$allShareAbout[] = $temp[$key];
					$ingroup = json_decode($value['viewuser'],true);
					foreach ($ingroup as $k => $v) {
						if (!in_array($v,$allgroup)) {
							$allgroup[] = $v;
						}
					}
				}
			}
			$res = [];
			foreach ($allAbout as $key => $value) {
				$res[$value['createtime']][$value['landuserid']] = $value;
				$datearr[] = $value['createtime'];
			}
			foreach ($allShareAbout as $key => $value) {
				$resshare[$value['createtime']][$value['landuserid']] = $value;
				$datesharearr[] = $value['createtime'];
			}
			for ($i=0; $i < $days; $i++) { 
				$date = date("Y-m-d",strtotime("+".$i."day",$startSec));
				if (!in_array($date,$datearr)) {
					$countInfo[$i]['createtime'] = $date;
					$countInfo[$i]['counts'] = 0;
				}else{
					$countInfo[$i]['createtime'] = $date;
					$countInfo[$i]['counts'] = count($res[$date]);
				}
				if (!in_array($date,$datesharearr)) {
					$countInfo[$i]['sharecounts'] = 0;
				}else{
					$countInfo[$i]['sharecounts'] = count($resshare[$date]);
				}
			}
			//我的分享
			// $nono['belongid'] = $info['pusherId'];
			// $nono['landuser.createtime'] = array('between',array($startTime,$endTime));
			// $nono['groupid'] = array('NEQ',0);
			$nogroup = DB::table('landuser')
			->where([
				['belongid', '=', $info['pusherId']],
				['groupid', '<>', 0],
			])
			->whereBetween('landuser.createtime',[$startTime,$endTime])
			->get();
			$nogroup = json_decode($nogroup,true);
			//我没有分享
			// $isnono['belongid'] = $info['pusherId'];
			// $isnono['groupid'] = 0;
			// $isnono['landuser.createtime'] = array('between',array($startTime,$endTime));
			$isnogroup = DB::table('landuser')
			->where([
				['belongid', '=', $info['pusherId']],
				['groupid', '=', 0],
			])
			->whereBetween('landuser.createtime',[$startTime,$endTime])
			->get();
			$isnogroup = json_decode($isnogroup,true);
			//别人分享
			foreach ($allgroup as $key => $value) {
				if ($value != $info['pusherId']) {
					$tempt[] = $value;
				}
			}
			if (empty($tempt)) {
				$othershisnogroup = [];
			}else{
				$othersh['groupid'] = array('NEQ',0);
				$othersh['belongid'] = array('in',$tempt);
				$othersh['createtime'] = array('between',array($startTime,$endTime));
				$othershisnogroup = DB::table('landuser')->where($othersh)->get();
			}
			$isall = count($allAbout);
			$mynocount = count($isnogroup);
			if ($isall == $mynocount) {
				$myshareCount = $mynocount;
				$otherShareCount = 0;
			}else{
				$myshareCount = count($nogroup);
				$otherShareCount = count($othershisnogroup);
			}
			$data['count']['allCount'] = count($allAbout);
			$data['count']['myshareCount'] = $myshareCount;
			$data['count']['otherShareCount'] = $otherShareCount;
			$data['count']['days'] = $days;
			$data['daysList'] = $countInfo;
			$code = 0;
			$message = "查询成功";
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 分享成果  用户搜索 actionType 1 全范围搜索 2 时间和名字搜索(传传要搜索日期当天的时间戳和名字)  3 时间搜索(传要搜索日期当天的时间戳)
	 * @return [type] [description]
	 */
	public function shareDetails()
	{
		$info = request();
		if (!empty($info['actionType']) && !empty($info['pusherId'])) {
			if (empty($info['endTime'])) {
				$endSec = time();
			}else{
				$endSec = $info['endTime'];
			}
			if (empty($info['days'])) {
				$days = 1;
			}else{
				$days = $info['days'];
			}
			$startSec = $endSec - (($days-1)*24*3600);
			$endTime = date('Y-m-d 23:59:59',$endSec);
			$startTime = date('Y-m-d 00:00:00',$startSec);
			$typeinfo = DB::table('typeinfo')->get();
			$typeinfo = json_decode($typeinfo,true);
			foreach ($typeinfo as $key => $value) {
				$typearr[$value['id']] = $value['name'];
			}
			$manageinfo = DB::table('manageinfo')->get();
			$manageinfo = json_decode($manageinfo,true);
			foreach ($manageinfo as $key => $value) {
				$managearr[$value['id']] = $value['name'];
			}
			$developmentinfo = DB::table('development')->get();
			$developmentinfo = json_decode($developmentinfo,true);
			foreach ($developmentinfo as $key => $value) {
				$developmentarr[$value['id']] = $value['name'];
			}
			$groupname = DB::table('groups')->select('groupid','name')->get();
			$groupname = json_decode($groupname,true);
			foreach ($groupname as $key => $value) {
				$grouparr[$value['groupid']] = $value['name'];
			}
			$sexarr = [
				'男'=>'0',
				'女'=>'1'
			];
			if ($info['actionType'] == 1) {
				if (!isset($info['search'])) {
					$code = -2001;
					$message = '请输入要搜索的名字或手机号';
					$data = [];
				}else{
					$search = $info['search'];
					$name['landuser.username'] = $search;
					$name['landuser.phone'] = $search;
					$name['_logic'] = 'or';
					$allno['_complex'] = $name;
					$allno['landuser.groupid'] = array('NEQ',0);
					$temp = DB::table('landuser')->join('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					->where([
						['landuser.username', '=', $search],
						['landuser.groupid', '<>', 0],
					])
					->orWhere([
						['landuser.phone', '=', $search],
						['landuser.groupid', '<>', 0],
					])
					->orderBy('landuser.createtime','desc')
					->get();
					$temp = json_decode($temp,true);
					// $data['test']['temp'] = $temp;
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								$data[$key]['pusherid'] = $temp[$key]['pusherid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}
			}else if($info['actionType'] == 2){
				if (isset($info['search']) && isset($info['currentSec'])) {
					$search = $info['search'];
					$currentDate = date('Y-m-d',$info['currentSec']);
					$name['landuser.username'] = $search;
					$name['landuser.phone'] = $search;
					$name['_logic'] = 'or';
					$allno['_complex'] = $name;
					$allno['landuser.groupid'] = array('NEQ',0);
					$allno['landuser.createtime'] = $currentDate;
					$temp = DB::table('landuser')->join('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					->where([
						['landuser.username', '=', $search],
						['landuser.groupid', '<>', 0],
						['landuser.createtime', '=', $currentDate],
					])
					->orWhere([
						['landuser.phone', '=', $search],
						['landuser.groupid', '<>', 0],
						['landuser.createtime', '=', $currentDate],
					])
					->orderBy('landuser.createtime','desc')
					->get();
					$temp = json_decode($temp,true);
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								$data[$key]['pusherid'] = $temp[$key]['pusherid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}else{
					$code = -2001;
					$message = '传入要搜索的时间戳或内容';
					$data = [];
					
				}
					
			}else{
				if (isset($info['currentSec'])) {
					$currentDate = date('Y-m-d',$info['currentSec']);
					// $allno['landuser.createtime'] = $currentDate;
					// $allno['landuser.groupid'] = array('NEQ',0);
					$temp = DB::table('landuser')->join('pusher','landuser.belongid','=','pusher.id')
					->select('pusher.id as pusherid','pusher.name as pushersname','landuser.groupid','landuser.username as landusername','landuser.sex','landuser.typeid','landuser.phone','landuser.province','landuser.city','landuser.area','landuser.address','landuser.manageid','landuser.development','landuser.content','landuser.createtime','landuser.viewuser')
					->where([
						['landuser.groupid', '<>', 0],
						['landuser.createtime', '=', $currentDate],
					])
					->get();
					$temp = json_decode($temp,true);
					if (!empty($temp)) {
						foreach ($temp as $key => $value) {
							if (in_array($info['pusherId'],json_decode($value['viewuser'],true)) || ($temp[$key]['groupid'] == 0 && $temp[$key]['pusherid'] == $info['pusherId'])) {
								$temp[$key]['typename'] = $typearr[$value['typeid']];
								$temp[$key]['sexname'] = $temp[$key]['sex'];
								foreach (json_decode($value['manageid'],true) as $k => $v) {
									// $temp[$key]['managename'][$v] = $managearr[$v];
									$temp[$key]['manageInfo'][$k]['manageid'] = $v;
									$temp[$key]['manageInfo'][$k]['managename'] = $managearr[$v];
								}
								foreach (json_decode($value['development'],true) as $k => $v) {
									// $temp[$key]['developmentname'][$v] = $developmentarr[$v];
									$temp[$key]['developmentInfo'][$k]['developmentid'] = $v;
									$temp[$key]['developmentInfo'][$k]['developmentname'] = $developmentarr[$v];
								}
								$temp[$key]['groupname'] = $grouparr[$value['groupid']];
								$data[$key]['pusherid'] = $temp[$key]['pusherid'];
								$data[$key]['pushername'] = $temp[$key]['pushersname'];
								$data[$key]['groupid'] = $temp[$key]['groupid'];
								$data[$key]['groupname'] = $temp[$key]['groupname'];
								$data[$key]['landusername'] = $temp[$key]['landusername'];
								$data[$key]['sexid'] = $sexarr[$temp[$key]['sex']];
								$data[$key]['sexname'] = $temp[$key]['sexname'];
								$data[$key]['typeid'] = $temp[$key]['typeid'];
								$data[$key]['typename'] = $temp[$key]['typename'];
								$data[$key]['landuserphone'] = $temp[$key]['phone'];
								$data[$key]['province'] = $temp[$key]['province'];
								$data[$key]['city'] = $temp[$key]['city'];
								$data[$key]['area'] = $temp[$key]['area'];
								$data[$key]['address'] = $temp[$key]['address'];
								$data[$key]['content'] = json_decode($temp[$key]['content'],true);
								$data[$key]['createtime'] = $temp[$key]['createtime'];
								$data[$key]['manageInfo'] = $temp[$key]['manageInfo'];
								$data[$key]['developmentInfo'] = $temp[$key]['developmentInfo'];
							}
						}
					}else{
						$data = [];
					}
					$code = 0;
					$message = '成功';
				}else{
					$code = -2001;
					$message = '缺少时间戳';
					$data = [];
				}
			}
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 手机验证码
	 * @return [type] [description]
	 */
	public function sendverifycode() {
		$info = request();
		if (!empty($info['phone'])) {
			$tel = $info['phone'];
			$yzm = \SendMsgService::GetfourStr(4);
			$res = \SendMsgService::sendMsg($tel,$yzm);
			$info = json_decode($res,true);
			$content = array('code'=>$info['code'],'yzm'=>$yzm);
			$data = $content;
			$code =0;
			$message = '成功';
		}else{
			$code = -1001;
			$message = '传参错误';
			$data = [];	
		}
		return dataBack($code,$message,$data);
	}
	/**
	 * 获取微信标签
	*/
	public function wxTages()
	{
		$wxobj = new WxinfoController;
		return response()->json($wxobj->wxTages());
	}
	/**
	 * 根据标签id获取标签下粉丝列表
	*/
	public function wxtagesInfo(Request $request)
	{
		$validatedData = $request->validate([
            'tagid' => 'required',
        ]);
		$info = request();
		$wxobj = new WxinfoController;
		return response()->json($wxobj->wxtagesInfo($info['tagid']));
	}
	/**
	 * 根据code获取openid
	*/
	public function wxOpenid(Request $request)
	{
		$validatedData = $request->validate([
            'code' => 'required',
        ]);
		$info = request();
		$wxobj = new WxinfoController;
		return response()->json($wxobj->wxOpenid($info['code']));
	}
	/**
	 * 接收code换取openid
	 * @return [type] [description]
	 */
	public function loginCode()
	{
		$info = request();
		if ((!empty($info['code']) || !empty($info['openid'])) && !empty($info['action_type'])) {
			$wxobj = new WxinfoController;
			if (!empty($info['openid'])) {
				$openid = $info['openid'];
			}else{
				$code = $info['code'];
				$openid = $wxobj->wxOpenid($code);
			}
			$alltagsinfo['systemuser'] = $wxobj->wxtagesInfo(104);//系统管理员
			$alltagsinfo['pushuser'] = $wxobj->wxtagesInfo(103);//地推
			if ($info['action_type'] == 1) {
				//id 104
				if (in_array($openid, $alltagsinfo['systemuser'])) {
					$code = 0;
					$message='系统管理员';
					$data['pusherInfo']['openid'] = $openid;
				}else{
					$code = 1111;
					$message='没有系统管理员权限';
					$data['pusherInfo']['openid'] = $openid;
				}
			}
			if ($info['action_type'] == 2) {
				//id 103
				if (in_array($openid, $alltagsinfo['pushuser']) || in_array($openid, $alltagsinfo['systemuser'])) {
					$temp = DB::table('pusher')->where('openid','=',$openid)->get();
					$temp = json_decode($temp,true);
					if (empty($temp)) {
						$code = 1111;
						$message='地推有权限,但是没有绑定手机号';
						$data['pusherInfo']['openid'] = $openid;
					}else{
						$code = 0;
						$message='地推有权限,手机号绑定过';
						$data['pusherInfo']['openid'] = $openid;
						$data['pusherInfo']['pusherId'] = $temp[0]['id'];
					}
				}else{
					$code = '-1111';
					$message='没有权限';
					$data['pusherInfo']['openid'] = $openid;
				}
			}
		}else{
			$code = '-3333';
			$message='传参错误';
			$data = [];
		}
		return dataBack($code,$message,$data);
	}
}
