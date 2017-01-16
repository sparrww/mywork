<?php
//
namespace Lib;

class Controller{

	/**
	 * 模版方法
	 *
	 * @return string
	 */
    public function display($path,$array=[]){

        global $_W;
        return Template::tpl($path);
	}


	/**
	 * 验证码
	 *
	 * @return string
	 */
	public static function validate(){
		$_vc = new validatecode();		//实例化一个对象
		return $_vc->doimg();
	}


	/**
	 * 加密解密
	 * @access	public
	 * @param	$array		type:string
	 * @param	$operation	type:E(加密) | D(解密)
	 * @param	$key		type:string 密钥
	 * @return	array
	 */
	function encrypt($string,$operation='D',$key=''){
	    $key=md5($key);
	    $key_length=strlen($key);
        $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
	    $string_length=strlen($string);
	    $rndkey=$box=array();
	    $result='';
	    for($i=0;$i<=255;$i++){
	           $rndkey[$i]=ord($key[$i%$key_length]);
	        $box[$i]=$i;
	    }
	    for($j=$i=0;$i<256;$i++){
	        $j=($j+$box[$i]+$rndkey[$i])%256;
	        $tmp=$box[$i];
	        $box[$i]=$box[$j];
	        $box[$j]=$tmp;
	    }
	    for($a=$j=$i=0;$i<$string_length;$i++){
	        $a=($a+1)%256;
	        $j=($j+$box[$a])%256;
	        $tmp=$box[$a];
	        $box[$a]=$box[$j];
	        $box[$j]=$tmp;
	        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	    }
	    if($operation=='D'){
	        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
	            return substr($result,8);
	        }else{
	            return'';
	        }
	    }else{
	        return str_replace('=','',base64_encode($result));
	    }
	}


    public function curlSubmit($url,$data=[]) {
        //对空格进行转义
        $url = str_replace(' ','+',$url);
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT,3); //定义超时3秒钟
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  //所需传的数组用http_bulid_query()函数处理
        //执行并获取url地址的内容
        $output = curl_exec($ch);
        $errorCode = curl_errno($ch);
        //释放curl句柄
        curl_close($ch);
        if(0 !== $errorCode) {
            return false;
        }
        return $output;
    }

}

