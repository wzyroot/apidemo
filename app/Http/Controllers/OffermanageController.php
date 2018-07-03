<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\WxinfoController;

class OffermanageController extends Controller
{
    public function offerManage()
	{
        $info = request();
		if (!empty($info['code']) || !empty($info['openid'])) {
            $wxobj = new WxinfoController;
			if (!empty($info['openid'])) {
				$openid = $info['openid'];
			}else{
				$code = $info['code'];
				$openid = $wxobj->wxOpenid($code);
			}
			$alltagsinfo['yijian'] = $wxobj->wxtagesInfo(102);//地推
			if (in_array($openid,$alltagsinfo['yijian'])) {
				$offerurl = 'http://192.168.0.119:3333/trade/offer/api?act=modifyGoodsOfferBySystem';
				// $offerurl = config('myconfig.yijianstophost').'trade/offer/api?act=modifyGoodsOfferBySystem';
				$data = [
					'action_type'=>'SOLD_OUT',
					'is_select_all'=>'1',
				];
				$info = http_post($offerurl,json_encode($data));
				$info = json_decode($info,true);
				$msg = $info['message'];
				$data['message'] = $msg;
			}else{
				$msg = '暂未开放';
				$data['message'] = $msg;
			}
			$data['openid'] = $openid;
		}else{
			$msg = '暂未开放';
			$data['message'] = $msg;
		}
		return response()->json($data);
	}
}
