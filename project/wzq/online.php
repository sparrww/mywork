<?php

    require __DIR__.'/../../workerman/vendor/GatewayClient/Gateway.php';
    use GatewayClient\Gateway;
    Gateway::$registerAddress = '127.0.0.1:1236';

	//处理qipan/order/win/flag数据的ajax请求
	if(isset($_POST)){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
	
		
		if($_POST['a']=='put'){

			$res = $db->query("UPDATE play SET
						qipan='{$_POST["qipan"]}',
						myorder='{$_POST["order"]}',
						win='{$_POST["win"]}',
						flag='{$_POST["flag"]}',
						back='{$_POST["back"]}'
					WHERE fromuid='{$_POST["fromuid"]}'");
			DB::unDB($res, $db);

            $res=$db->query("SELECT qipan,myorder,flag,win,back FROM play WHERE fromuid='{$_POST["fromuid"]}' LIMIT 1");
            $row=$res->fetch_object();

            Gateway::sendToUid($_POST["fromuid"], $row->qipan.'|'.$row->myorder.'|'.$row->flag.'|'.$row->win.'|'.$row->back);
		//echo $db->affected_rows;//执行成功会返回0，这是mysql的原因
		}else if($_POST['a']=='update'){
			
			//echo file_get_contents('data/qipan.txt').'|'.file_get_contents('data/order.txt').'|'.file_get_contents('data/flag.txt').'|'.file_get_contents('data/win.txt').'|'.file_get_contents('data/back.txt');
			$res=$db->query("SELECT qipan,myorder,flag,win,back FROM play WHERE fromuid='{$_POST["fromuid"]}' LIMIT 1");
			$row=$res->fetch_object();
			echo $row->qipan.'|'.$row->myorder.'|'.$row->flag.'|'.$row->win.'|'.$row->back;

			DB::unDB($res, $db);
		}else if($_POST['a']=='delete'){//更新棋局
// 			unlink('data/qipan.txt');
// 			unlink('data/order.txt');
// 			unlink('data/win.txt');
// 			unlink('data/flag.txt');
		}else if($_POST['a']=='setWin'){
			//file_put_contents('data/win.txt','0');
		}else if($_POST['a']=='store'){	
			$res=$db->query("SELECT store FROM play WHERE fromuid='{$_POST["fromuid"]}'");
			$row=$res->fetch_object();
			$text=$row->store.'&'.$_POST['qipan'].'|'.$_POST['order'];
			$db->query("UPDATE play SET store='$text' WHERE fromuid='{$_POST["fromuid"]}'");
			DB::unDB($res, $db);
		}
		
	}

?>