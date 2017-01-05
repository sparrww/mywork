<?php
class Api
{
    public $ipUrl = 'http://apis.juhe.cn/ip/ip2addr?key=94af64cbfd39d927f3bcf484fecbaa6a&ip=';  //ip接口
    public $phoneUrl = 'http://apis.juhe.cn/mobile/get?key=23b7c9be87091d4d7f0094333b7a2591&phone=';  //归属地接口
    public $cardUrl = 'http://apis.juhe.cn/idcard/index?key=f8f5f750cd8749da005e738087c159ab&cardno=';


    /**
     * @param $ip
     * @return mixed
     *  ["resultcode"] => string(3) "200"
        ["reason"] => string(16) "Return Successd!"
        ["result"] => array(2) {
            ["area"] => string(18) "陕西省西安市"
            ["location"] => string(40) "移动GSM/TD-SCDMA/LTE全省共用出口"
        }
        ["error_code"] => int(0)
     */
    public function getIp($ip){
        $result = Post($this->ipUrl,$ip);
        return json_decode($result,true);
    }


    public function getPhone($phone){
        $result = Post($this->phoneUrl,$phone);
        return json_decode($result,true);
    }

}