<?php
class Api
{
    public $ipUrl = 'http://apis.juhe.cn/ip/ip2addr?key=94af64cbfd39d927f3bcf484fecbaa6a&ip=';  //ip接口
    public $phoneUrl = 'http://apis.juhe.cn/mobile/get?key=23b7c9be87091d4d7f0094333b7a2591&phone=';  //归属地接口
    public $cardUrl = 'http://apis.juhe.cn/idcard/index?key=f8f5f750cd8749da005e738087c159ab&cardno=';


    public function getIp($ip){
        $result = iCurl($this->ipUrl,$ip);
        return json_decode($result,true);
    }

}