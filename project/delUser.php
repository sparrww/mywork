<?php
	//ajax请求页面,当用户关闭游戏时，删除用户的id
	if (isset($_POST['userid'])){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
		$db->query("DELETE FROM user WHERE userid='{$_POST['userid']}'");
		DB::unDB('', $db);

	}
?>