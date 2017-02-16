<?php
	//处理还原操作的ajax请求
	if(isset($_POST)){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
		$optval=$_POST['optval'];
		$optval=$optval<=0?1:$optval;

		$res=$db->query("SELECT store FROM play WHERE fromuid='{$_POST["fromuid"]}' LIMIT 1");
		$row=$res->fetch_object();
		$store=$row->store;
		if($store){
			if(substr($store,0,1)=='&'){
				$store=substr($store,1);
			}
			$store=explode('&',$store);
			if($optval<=count($store)){
				echo $store[$optval-1];
			}else{
				echo '1';//表示用户选定的局数不存在
			}
		}else{
			echo '2';//表示该用户还没有对战信息
		}
		
	}
?>