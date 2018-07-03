<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\WxinfoController;

class DatacenterController extends Controller
{
	/**
	 * 权限判断
	*/
	public function jdugeAuth()
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
			$alltagsinfo['charttable'] = $wxobj->wxtagesInfo(105);//地推
			if (in_array($openid,$alltagsinfo['charttable'])) {
				$msg = '有权限';
				$data['message'] = $msg;
				$code = 0;				
			}else{
				$msg = '暂未开放';
				$data['message'] = $msg;
				$code = 1;
			}
			$data['openid'] = $openid;
		}else{
			$msg = '暂未开放1';
			$data['message'] = $msg;
			$code = 2;
		}
		return response()->json($data);
	}
    /**
	 * 用户统计
	 * @return [type] [description]
	 */
	public function userInfo()
	{
        $credentials = request();
        // return $credentials;
		if (empty($credentials['days'])) {
			$days = 7;
		}else{
			$days = $credentials['days'];
		}
		if (empty($credentials['end_time'])) {
			$end_time = time();
		}else{
			$end_time = $credentials['end_time'];
		}
		$data = [
			'days' => $days,
			'end_time' => $end_time
		];
		// $sign = funongSing($data,$this->appsecret);
		$urlnews_list = config('myconfig.datacenter_host')."dealers/contract/api?act=countAccount";
		$articles = http_post($urlnews_list,json_encode($data));
		// dump($articles);exit;response()->json(['error' => $articles], 401)
        return $articles;
    }
    /**
	 * 采/销平衡
	 * @return [type] [description]
	 */
	public function salePurchase()
	{
		$credentials = request();
        // return $credentials;
		if (empty($credentials['days'])) {
			$days = 7;
		}else{
			$days = $credentials['days'];
		}
		if (empty($credentials['end_time'])) {
			$end_time = time();
		}else{
			$end_time = $credentials['end_time'];
		}
		$data = [
			'days' => $days,
			'end_time' => $end_time
		];
		// $sign = $this->funongSing($data,$this->appsecret);
		$urlnews_list = config('myconfig.datacenter_host')."dealers/contract/api?act=countContractNum";
        $datainfo = http_post($urlnews_list,json_encode($data));

        $datainfo = json_decode($datainfo,true);
        $datainfo = $datainfo['data'];

		foreach ($datainfo['purchase'] as $key => $value) {
			$datainfo['purchase'][$key]['purchaseamount_num'] = $value['amount_num'];
			$datainfo['purchase'][$key]['purchaseamount_money'] = $value['amount_money'];
			unset($datainfo['purchase'][$key]['amount_num']);
			unset($datainfo['purchase'][$key]['amount_money']);
		}
		foreach ($datainfo['sale'] as $k => $v) {
			$datainfo['sale'][$k]['saleamount_num'] = $v['amount_num'];
			$datainfo['sale'][$k]['saleamount_money'] = $v['amount_money'];
			unset($datainfo['sale'][$k]['amount_num']);
			unset($datainfo['sale'][$k]['amount_money']);
		}
		$data = array_merge($datainfo['purchase'],$datainfo['sale']);
		$info = [];
		foreach ($data as $x => $y) {
			$info[$y['good_id']][] = $y;
		}
		$temp = [];
		foreach ($info as $a => $b) {
			if (count($b) == 2) {
				$temp['data']['offer_list'][] = array_merge($b[0],$b[1]);
			}else{
				foreach ($b as $key => $value) {
					$temp['data']['offer_list'][] = $value;
				}
			}
		}
		$temp['data']['purchase_count'] = $datainfo['purchase_count'];
		$temp['data']['sale_count'] = $datainfo['sale_count'];
        return $temp;
    }
    /**
	 * 销售合同开单统计
	 * @return [type] [description]
	 */
	public function saleopenOrder()
	{
		$credentials = request();
        // return $credentials;
		if (empty($credentials['days'])) {
			$days = 7;
		}else{
			$days = $credentials['days'];
		}
		if (empty($credentials['end_time'])) {
			$end_time = time();
		}else{
			$end_time = $credentials['end_time'];
		}
		if (empty($credentials['kind']) || $credentials['kind'] == '全部品种') {
			$data = [
				'days' => $days,
				'end_time' => $end_time
			];
		}else{
			$kind = $credentials['kind'];
			$data = [
				'days' => $days,
				'end_time' => $end_time,
				'kind' => $kind
			];
		}
		// dump($data);
		// $sign = $this->funongSing($data,$this->appsecret);
		$urlnews_list = config('myconfig.datacenter_host')."/dealers/contract/api?act=countSellOrder";
		// $urlnews_list = "http://192.168.0.119:2222/dealers/contract/api?act=countSellOrder&appid=".$this->appid."&sign=".$sign;
		$articles = apiIsOk(http_post($urlnews_list,json_encode($data)));
		if (isset($articles['kinds'])) {
			array_unshift($articles['kinds'],'全部品种');
		}
        // dump($articles);exit;
        return $articles;
		// $this->ajaxReturn($articles);
    }
    /**
	 * 采购开单统计
	 * @return [type] [description]
	 */
	public function purchaseOpenorder()
	{
		$credentials = request();
        // return $credentials;
		if (empty($credentials['days'])) {
			$days = 7;
		}else{
			$days = $credentials['days'];
		}
		if (empty($credentials['end_time'])) {
			$end_time = time();
		}else{
			$end_time = $credentials['end_time'];
		}
		if (empty($credentials['kind']) || $credentials['kind'] == '全部品种') {
			$data = [
				'days' => $days,
				'end_time' => $end_time
			];
		}else{
			$kind = $credentials['kind'];
			$data = [
				'days' => $days,
				'end_time' => $end_time,
				'kind' => $kind
			];
		}
		// dump($data);
		// $sign = $this->funongSing($data,$this->appsecret);
		$urlnews_list = config('myconfig.datacenter_host')."dealers/contract/api?act=countPurchaseOrder";
		$articles = apiIsOk(http_post($urlnews_list,json_encode($data)));
		if (isset($articles['kinds'])) {
			array_unshift($articles['kinds'],'全部品种');
		}
        return $articles;
    }
    /**
	 * 销售统计
	 * @return [type] [description]
	 */
	public function saleInfo()
	{
		$credentials = request();
        // return $credentials;
		if (empty($credentials['days'])) {
			$days = 7;
		}else{
			$days = $credentials['days'];
		}
		if (empty($credentials['end_time'])) {
			$end_time = time();
		}else{
			$end_time = $credentials['end_time'];
		}
		$data = [
			'days' => $days,
			'end_time' => $end_time
		];
		// $sign = $this->funongSing($data,$this->appsecret);
		$urlnews_list = config('myconfig.datacenter_host')."dealers/contract/api?act=countDeposit";
		$articles = apiIsOk(http_post($urlnews_list,json_encode($data)));
        return $articles;
    }
    /**
	 * 浮盈浮亏
	 * @return [type] [description]
	 */
	public function countProfitAndLoss()
	{
		$credentials = request();
		if(!empty($credentials['type'])){
			$type = $credentials['type'];
			if($type == 'CUSTOM'){
				$start = $credentials['start'];
				$end = $credentials['end'];
				$data = [
					'type' => $credentials['type'],
					'end' => $end,
					'start' => $start,
				];
			}else{
				$data = [
					'type' => $type,
				];
			}
			// $sign = $this->funongSing($data,$this->appsecret);
			$urlnews_list = config('myconfig.datacenter_host')."/dealers/contract/api?act=countProfitAndLoss";
			$res = apiIsOk(http_post($urlnews_list,json_encode($data)));
			if(isset($res['this_month']) && isset($res['last_month'])){
				$initall = $res['init_data'];
				$infoinit = [];
				foreach($initall as $k=>$v){
					if(!isset($infoinit[$k])){
						$infoinit[$v['delivery_address']]=$initall[$k]['money']; 
					}
				}
				$tempt['this_month'] = $this->tempData($res['this_month']);
				$tempt['last_month'] = $this->tempData($res['last_month']);
				$finall = $this->dataSettlement($tempt);
				$lasttrancemony = 0;
				$thistrancemony = 0;
				$lasttrancenum = 0;
				$thistrancenum = 0;
				$floatloss = 0;
				foreach($finall as $key => $value){
					$finall[$key]['thisprice'] = $value['this_transfer_price'];
					$finall[$key]['lastprice'] = $value['last_transfer_price'];
					
					$last1 = round($value['last_saleamount_num'] - $value['last_purchaseamount_num']);
					$finall[$key]['lastknots_amontsall'] = $last1;
					//上月结转额
					$last2 = round($last1 * $value['last_transfer_price']);
					if(isset($infoinit[$key])){
						$finall[$key]['lastknots_cashall1111111'] = $last2;
						$last2 -= $infoinit[$key];
					}
					$finall[$key]['lastknots_cashall'] = round($last2);
					//结转量
					$last3 = round($last1 - $value['this_purchaseamount_num'] + $value['this_saleamount_num']);
					$finall[$key]['thisknots_amontsall'] = $last3;
					//结转额
					$last4 = round($last3 * $value['this_transfer_price']);
					$finall[$key]['thisknots_cashall'] = $last4;
					//浮盈浮亏额
					$abs = round($last2 +  $value['this_saleamount_money']);//0+84150
					$bbs = round($value['this_purchaseamount_money'] + $last4);//0+0
					$finall[$key]['profit_loss'] = round($abs - $bbs);
					$lasttrancemony += $last2;
					$thistrancemony += $last4;
					$lasttrancenum += $last1;
					$thistrancenum += $last3;
					$floatloss += $finall[$key]['profit_loss'];
				}
				foreach($finall as $k => $v){
					$finalkey[] = $v;
				}
				$nextarr['data']['allstatistics'] = [
					'lastknots_amontsall' => round($lasttrancenum),
					'lastknots_cashall' => round($lasttrancemony),
					'thisknots_amontsall' => round($thistrancenum),
					'thisknots_cashall' => round($thistrancemony),
					'profit_loss' => round($floatloss),
					'this_purchaseamount_money' => round($res['this_month']['purchase_count']['purchase_total_money']),
					'this_saleamount_money' => round($res['this_month']['sale_count']['sale_total_money']),
				];
				$nextarr['data']['liststatistics'] = $finalkey;
			}
		}else{
			$nextarr['msg'] = '参数错误';
		}
        return $nextarr;
	}
	/**
	 * 数据处理
	*/
	public function tempData($res)
	{
		$data['purchase'] = $this->salePurchaTemp($res['purchase']);
		$data['sale'] = $this->salePurchaTemp($res['sale']);
		// return $data;
		foreach ($data['purchase'] as $key => $value) {
			$data['purchase'][$key]['purchaseamount_num'] = $value['amount_num'];
			$data['purchase'][$key]['purchaseamount_money'] = $value['amount_money'];
			unset($data['purchase'][$key]['amount_num']);
			unset($data['purchase'][$key]['amount_money']);
		}
		foreach ($data['sale'] as $k => $v) {
			$data['sale'][$k]['saleamount_num'] = $v['amount_num'];
			$data['sale'][$k]['saleamount_money'] = $v['amount_money'];
			unset($data['sale'][$k]['amount_num']);
			unset($data['sale'][$k]['amount_money']);
		}
		// $thisinfos = array_merge($data['purchase'],$data['sale']);
		$info = [];
		foreach($data['sale'] as $k=>$v){
			if(!isset($info[$k])){
				$info[$k]['saleamount_num']=$data['sale'][$k]['saleamount_num']; 
				$info[$k]['saleamount_money']=$data['sale'][$k]['saleamount_money']; 
			}
		}
		foreach($data['purchase'] as $k=>$v){
			if(!isset($info[$v['delivery_address']])){
				$info[$k]['saleamount_num']=0; 
				$info[$k]['saleamount_money']=0; 
				$info[$k]['purchaseamount_num']=$data['purchase'][$k]['purchaseamount_num']; 
				$info[$k]['purchaseamount_money']=$data['purchase'][$k]['purchaseamount_money']; 
			}else{
				if(!isset($info[$k]['purchaseamount_money']) || !isset($info[$k]['purchaseamount_num'])){
					$info[$k]['purchaseamount_num']=$data['purchase'][$k]['purchaseamount_num']; 
					$info[$k]['purchaseamount_money']=$data['purchase'][$k]['purchaseamount_money']; 
				}
			}
		}
		foreach($info as $a=>$b){
			if(!isset($info[$a]['purchaseamount_money']) || !isset($info[$a]['purchaseamount_num'])){
				$info[$a]['purchaseamount_num'] = 0; 
				$info[$a]['purchaseamount_money'] = 0; 
			}
			if(!isset($info[$a]['saleamount_money']) || !isset($info[$a]['saleamount_num'])){
				$info[$a]['saleamount_num'] = 0; 
				$info[$a]['saleamount_money'] = 0; 
			}
			$info[$a]['name'] = str_replace("油厂","",$a);
			if(!isset($data['purchase'][$a]['transfer_price'])){
				$info[$a]['transfer_price'] = $data['sale'][$a]['transfer_price'];
			}else{
				$info[$a]['transfer_price'] = $data['purchase'][$a]['transfer_price'];
			}
			
			
		}
		return $info;
	}
	public function salePurchaTemp($res)
	{
		$temp = [];
		foreach($res as $k=>$v){
			if(!isset($temp[$v['delivery_address']])){
				$temp[$v['delivery_address']]=$v; 
			}else{
				$temp[$v['delivery_address']]['amount_num']+=$v['amount_num'];
				$temp[$v['delivery_address']]['amount_money']+=$v['amount_money'];
			}
		}
		return $temp;
	}
	public function dataSettlement($temp)
	{
		$info = [];
		foreach ($temp['this_month'] as $k => $v) {
			if(!isset($info[$k])){
				$info[$k]['this_saleamount_num'] = round($v['saleamount_num']);
				$info[$k]['this_saleamount_money'] = round($v['saleamount_money']);
				$info[$k]['this_purchaseamount_num'] = round($v['purchaseamount_num']);
				$info[$k]['this_purchaseamount_money'] = round($v['purchaseamount_money']);
			}
		}
		foreach ($temp['last_month'] as $k => $v) {
			if(!isset($info[$k])){
				$info[$k]['last_saleamount_num'] = round($v['saleamount_num']);
				$info[$k]['last_saleamount_money'] = round($v['saleamount_money']);
				$info[$k]['last_purchaseamount_num'] = round($v['purchaseamount_num']);
				$info[$k]['last_purchaseamount_money'] = round($v['purchaseamount_money']);

				$info[$k]['this_saleamount_num'] = 0;
				$info[$k]['this_saleamount_money'] = 0;
				$info[$k]['this_purchaseamount_num'] = 0;
				$info[$k]['this_purchaseamount_money'] = 0;
			}else{
				if(!isset($info[$k]['last_saleamount_num']) || !isset($info[$k]['last_purchaseamount_num'])){
					$info[$k]['last_saleamount_num'] = round($v['saleamount_num']);
					$info[$k]['last_saleamount_money'] = round($v['saleamount_money']);
					$info[$k]['last_purchaseamount_num'] = round($v['purchaseamount_num']);
					$info[$k]['last_purchaseamount_money'] = round($v['purchaseamount_money']);
				}
			}
		}
		foreach($info as $a => $b){
			if(!isset($info[$a]['last_saleamount_num']) || !isset($info[$a]['last_purchaseamount_num'])){
				$info[$a]['last_saleamount_num'] = 0;
				$info[$a]['last_saleamount_money'] = 0;
				$info[$a]['last_purchaseamount_num'] = 0;
				$info[$a]['last_purchaseamount_money'] = 0;
			}
			if(!isset($info[$a]['this_saleamount_num']) || !isset($info[$a]['this_purchaseamount_num'])){
				$info[$a]['this_saleamount_num'] = 0;
				$info[$a]['this_saleamount_money'] = 0;
				$info[$a]['this_purchaseamount_num'] = 0;
				$info[$a]['this_purchaseamount_money'] = 0;
			}
			$info[$a]['name'] = str_replace("油厂","",$a);
			if(!isset($temp['this_month'][$a]['transfer_price'])){
				$info[$a]['this_transfer_price'] = 0;
			}else{
				$info[$a]['this_transfer_price'] = $temp['this_month'][$a]['transfer_price'];
			}
			if(!isset($temp['last_month'][$a]['transfer_price'])){
				$info[$a]['last_transfer_price'] = 0;
			}else{
				$info[$a]['last_transfer_price'] = $temp['last_month'][$a]['transfer_price'];
			}
		}
		return $info;
	}
}
