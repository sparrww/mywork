<?php
if(isset($_POST)){
	require('DB.class.php');//引入数据库类
	$db=DB::getDB();
	$res=$db->query("SELECT userid FROM user");
	$allUser='';//存放用户id的字符串
	if($res){
		while (!!$row=$res->fetch_array()){
		
			$allUser.='|'.$row['userid'];
		}
		if(substr($allUser,0,1)=='|'){
			$allUser=substr($allUser, 1);
		}
		echo $allUser;
	}
	DB::unDB($res, $db);
}

?>