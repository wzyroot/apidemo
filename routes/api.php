<?php

use Illuminate\Http\Request;
// use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'prefix' => 'auth',
    'middleware' => 'checkToken'
], function ($router) {
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});
//登录组件
Route::group([
    'prefix' => 'auth',
    // 'middleware' => 'checkToken'
], function ($router) {
    Route::any('login', 'AuthController@login');
});
Route::group([
    'middleware' => 'checkToken',
    'prefix' => 'dataCenter'
], function ($router) {
    Route::post('jdugeAuth', 'DatacenterController@jdugeAuth');//权限判断
    Route::post('userInfo', 'DatacenterController@userInfo');//用户统计
    Route::post('salePurchase', 'DatacenterController@salePurchase');//采/销平衡
    Route::post('saleopenOrder', 'DatacenterController@saleopenOrder');//销售合同开单统计
    Route::post('purchaseOpenorder', 'DatacenterController@purchaseOpenorder');//采购开单统计
    Route::post('saleInfo', 'DatacenterController@saleInfo');//销售统计
    Route::post('countProfitAndLoss', 'DatacenterController@countProfitAndLoss');//浮盈浮亏
});
Route::group([
    'prefix' => 'landPush'
], function ($router) {
    Route::post('loginCode', 'LandpushController@loginCode');//接收code换取openid,判断有无地推权限
    Route::post('addDevelopment', 'LandpushController@addDevelopment');//添加发展成果
    Route::post('addManageinfo', 'LandpushController@addManageinfo');//添加经营类型
    Route::post('addTypeinfo', 'LandpushController@addTypeinfo');//添加发展成果
    Route::post('addGroup', 'LandpushController@addGroup');//添加用户
    Route::any('allGroup', 'LandpushController@allGroup');//查询组
    Route::any('addPusher', 'LandpushController@addPusher');//添加分组
    Route::any('groupSearch', 'LandpushController@groupSearch');//小组搜索(模糊)
    Route::any('groupDetails', 'LandpushController@groupDetails');//小组详情
    Route::any('groupUserSearch', 'LandpushController@groupUserSearch');//小组成员搜索
    Route::any('groupUserRemove', 'LandpushController@groupUserRemove');//小组成员移除
    Route::any('groupUserAdd', 'LandpushController@groupUserAdd');//小组成员添加
    Route::any('selectUserName', 'LandpushController@selectUserName');//除该小组外的所有成员
    Route::any('groupNameEdit', 'LandpushController@groupNameEdit');//修改名称
    Route::any('deleteGroup', 'LandpushController@deleteGroup');//删除小组
    Route::any('allGroupOptions', 'LandpushController@allGroupOptions');//组选项
    Route::any('addUser', 'LandpushController@addUser');//成员添加
    Route::any('userList', 'LandpushController@userList');//成员列表
    Route::any('groupToUser', 'LandpushController@groupToUser');//跟据组查成员
    Route::any('searchUser', 'LandpushController@searchUser');//跟据手机号或者姓名查成员
    Route::any('pusherDelete', 'LandpushController@pusherDelete');//地拖成员删除
    Route::any('editStatus', 'LandpushController@editStatus');//改变成员状态 
    Route::any('userGroup', 'LandpushController@userGroup');//成员分组
    Route::any('editPusher', 'LandpushController@editPusher');//修改地推者信息
    Route::any('systemData', 'LandpushController@systemData');//系统管理中的数据统计
    Route::any('reportDetails', 'LandpushController@reportDetails');//上报详情
    Route::any('allManageDevelopment', 'LandpushController@allManageDevelopment');//经营品种和拓展成果所有类
    Route::any('addReport', 'LandpushController@addReport');//新增上报
    Route::any('phoneBind', 'LandpushController@phoneBind');//手机号绑定
    Route::any('landuserReport', 'LandpushController@landuserReport');//用户上报统计
    Route::any('landuserSearch', 'LandpushController@landuserSearch');//用户搜索
    Route::any('editReport', 'LandpushController@editReport');//修改上报信息
    Route::any('deleteReport', 'LandpushController@deleteReport');//删除上报信息
    Route::any('addContent', 'LandpushController@addContent');//添加备注信息
    Route::any('achieveData', 'LandpushController@achieveData');//成果统计
    Route::any('shareDetails', 'LandpushController@shareDetails');//分享成果
    Route::any('sendverifycode', 'LandpushController@sendverifycode');//手机验证码
    Route::any('wxTages', 'LandpushController@wxTages');//获取微信标签
    Route::any('wxtagesInfo', 'LandpushController@wxtagesInfo');//根据标签id获取标签下粉丝列表
    Route::any('wxOpenid', 'LandpushController@wxOpenid');//根据code获取openid
});
Route::group([
    // 'middleware' => 'checkToken',
    'prefix' => 'wxinfo'
], function ($router) {
    Route::post('wxTages', 'WxinfoController@wxTages');//获取标签
});
Route::group([
    // 'middleware' => 'checkToken',
    'prefix' => 'offermanage'
], function ($router) {
    Route::any('offerManage', 'OffermanageController@offerManage');//一键停售
});
Route::any('weixin/api', 'WeixinController@api');//微信




