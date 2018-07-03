<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WxinfoController extends Controller
{
    /**
     * CURL请求
     * @param string $url
     * @param array $data
     * @param bool $is_post_request = false
     * @return mixed
     */
    public function curlRequest($url, $data, $is_post_request = false)
    {
        if (false === $is_post_request) {
            $url .= '&' . http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($is_post_request) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        if (false === $result) {
            return [];
        }
        $json = json_decode($result, true);
        if (null === $json) {
            return $result;
        }
        return $json;
    }
    /**
     * URL拼接
     * @param string $host
     * @param string $api_url
     * @param string $wechat_id
     * @param string $_key
     * @param string $_sign
     * @param int $_timestamp
     * @return string
     */
    public function toUrl($host, $api_url, $wechat_id, $_key, $_sign, $_timestamp)
    {
        $url = $host . $api_url . '?_key=' . $_key . '&_timestamp=' . $_timestamp
            . '&wechat_id=' . $wechat_id . '&_sign=' . urlencode($_sign);
        return $url;
    }

    /**
     * 参数编码
     * @param string $string
     * @return string
     */
    public function _percentEncode($string) : string
    {
        $res = urlencode($string);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * 生成签名
     * @param string $_key
     * @param string $_secret
     * @param string $wechat_id
     * @param int $_timestamp
     * @param array $data
     * @return string
     */
    public function createSign($_key, $_secret, $wechat_id, $_timestamp, $data)
    {
        $parameter = [
            '_key' => $_key,
            '_secret' => $_secret,
            'wechat_id' => $wechat_id,
            '_timestamp' => $_timestamp
        ];
        $parameter = array_merge($parameter, $data); //合并数组
        ksort($parameter); //对键名排序
        $query_string = ''; //对参数数组转成&name=value字符串，同时对value进行编码
        foreach ($parameter as $k => $v) {
            $query_string .= '&' . $k . '=' . $this->_percentEncode($v);
        }
        $string_to_sign = $wechat_id . '&%2F&' . $this->_percentEncode(substr($query_string, 1)); //微信ID+字符串+去除第一位&字符串的“参数字符串”
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $_secret . '&', true)); //sha1后base64编码
        return $signature;
    }
    /**
	 * 获取标签
	*/
	public function wxTages()
	{
		$timestamp = time();
		$data = [];
		$api_url = '/wechat_user/tag_list_query.json';
		$sign = $this->createSign(config('myconfig.fskey'), config('myconfig.fssecret'), config('myconfig.wechatid'), $timestamp, $data);
		$url = $this->toUrl(config('myconfig.fshost'), $api_url, config('myconfig.wechatid'), config('myconfig.fskey'), $sign, $timestamp);
		$result = $this->curlRequest($url, $data, false);
		return $result;
	}
	/**
	 * 获取标签内容
	*/
	public function wxtagesInfo($tigid)
	{
		$timestamp = time();
		$data = [
			'tagid' => $tigid,
		];
		$api_url = '/wechat_user/tag_user_openid_list_query.json';
		$sign = $this->createSign(config('myconfig.fskey'), config('myconfig.fssecret'), config('myconfig.wechatid'), $timestamp, $data);
		$url = $this->toUrl(config('myconfig.fshost'), $api_url, config('myconfig.wechatid'), config('myconfig.fskey'), $sign, $timestamp);
		$result = $this->curlRequest($url, $data, false);
		$openarr = $result['resource']['openid'];
		return $openarr;
	}
	/**
	 * 获取openid
	*/
	public function wxOpenid($code)
	{
		$timestamp = time();
		$data = [
			'code' => $code,
		];
		$api_url = '/web_wechat/wechat_user_access_token_query.json';
		$sign = $this->createSign(config('myconfig.fskey'), config('myconfig.fssecret'), config('myconfig.wechatid'), $timestamp, $data);
		$url = $this->toUrl(config('myconfig.fshost'), $api_url, config('myconfig.wechatid'), config('myconfig.fskey'), $sign, $timestamp);
		$result = $this->curlRequest($url, $data, false);
		$openid = $result['resource']['openid'];
		return $openid;
	}
}
