<?php
class SendMsgService
{
    protected  static $url = 'https://app.efunong.com/PHPSmser/sendMsg.php';
    protected  static $callback_url ='http://bl.cn/callBackTc.php';
    static function sendMsg($phone_list,$params)
    {
        $data = array("act" => 'sendValidCodeSMS',"phone" => $phone_list,"params" => [$params],"callback_url" => self::$callback_url);
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    static function GetfourStr($len){
        $chars_array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $charsLen = count($chars_array) - 1;
  
        $outputstr = "";
        for ($i=0; $i<$len; $i++){
            $outputstr .= $chars_array[mt_rand(0, $charsLen)];
        }
        return $outputstr;
    }
}
?>