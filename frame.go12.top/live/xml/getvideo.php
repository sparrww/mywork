<?php
$callback = $_REQUEST['callback'];
$vid = $_REQUEST['vid'];
/*
$thisUrl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$urlParms=getUrlParms($thisUrl);
$vid=$urlParms["vid"];
//获取参数组
function getUrlParms($str){
    $data = array();
    $parameter = explode('&',end(explode('?',$str)));
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}
*/

//自定义视频参数数组项，可从服务器数据库获取
$data_array = array(
	array(
    'vid' => '4',				//监控直播ID号，唯一
	'ip' => 'v1.insytone.cn',	//服务器IP或url
	"port"=>"2005",
    'user' => 'jkzxzb3',			//用户名
	'password' => '',		//密码
    ),
	array(
    'vid' => '5',				//监控直播ID号，唯一
	'ip' => 'v1.insytone.cn',	//服务器IP或url
    'user' => 'jkzxzb2',		//用户名
	'password' => '',			//密码
    ),
	array(
    'vid' => '6',				//监控直播ID号，唯一
	'ip' => 'v1.insytone.cn',	//服务器IP或url
    'user' => 'jkzxzb3',		//用户名
    )
);

//查询匹配的数组
foreach ($data_array as $data) {
	if ($vid==$data['vid']){
		$output=$data;
		break;
	}
}

//输出JSON
if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($output) . ');';
} else {
    header('Content-Type: application/x-json');
    echo json_encode($output);
	//{"ip":"v1.insytone.cn","port":"2005","user":"jkzxzb3","password":"","dev":""}
}

?>

