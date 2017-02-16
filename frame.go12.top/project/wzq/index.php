<?php
	//首页
	session_start();
	header("Content-Type:text/html;charset=utf-8");
	require('getRandOnlyId.php');//引入获取唯一ID的函数
	require('DB.class.php');//引入数据库类

	if(empty($_SESSION['uid'])){
		$userid = strval(getRandOnlyId());//用户id;
		$_SESSION['uid'] = $userid;
	}else{
		$userid = $_SESSION['uid'];
	}

	
	//数据库资源句柄
	$db=DB::getDB();
	$db->query("INSERT INTO user (userid) VALUES ('$userid')");
	if($db->affected_rows!=1){
		exit('用户数据写入失败，请重刷页面');
	}
	
	//获取当前所有在线用户
	$allUser=array();
	$res=$db->query("SELECT userid FROM user");
	if($res){
		while (!!$row=$res->fetch_array()){
		
			$allUser[]=$row['userid'];
		}
		
	}else{
		exit('没有用户');
	}
	//销毁
	DB::unDB($res, $db);
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>五子棋</title>
<link rel="stylesheet" type="text/css" href="css/index.css">
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/index.js"></script>
</head>
<body>

	<script type="text/javascript">
		ws = new WebSocket("ws://"+document.domain+":7272");
		// 服务端主动推送消息时会触发这里的onmessage
		ws.onmessage = function(e){
			// json数据转换成js对象
			var data = eval("("+e.data+")");
			var type = data.type || '';
			switch(type){
				// Events.php中返回的init类型的消息，将client_id发给后台进行uid绑定
				case 'init':
					// 利用jquery发起ajax请求，将client_id发给后端进行uid绑定
					$.post("http://"+document.domain+'/bind.php', {client_id: data.client_id}, function(data){}, 'json');
					break;
				// 当mvc框架调用GatewayClient发消息时直接alert出来
			}
		};
	</script>
	
	<header>
		<h1>五子棋游戏</h1>
	</header>
	<div id="state">
		<h2>游戏说明</h2>
		<p>1.进入游戏后，您必须先邀请一位在线用户才可以游戏。若没有在线用户，可以再打开一个游戏页面模拟在线用户，之后点击右上角的刷新图标就可以看到另一位在线用户的ID；</p>
		<p>2.点击“邀请”后对方若迟迟没有回应，可以刷新页面重新邀请；对方可以通过点击“查看邀请”来接受或拒绝邀请</p>
		<p>3.点击“查看邀请”按钮确认对方接受邀请后方可下子；</p>
		<p>4.对战双方都必须点击“开始下子”按钮，棋局才真正开始。游戏默认黑方先下子；</p>
		<p>5.游戏用户可以悔棋一步；</p>
		<p>6.下棋期间不可清局；</p>
		<p>7.下棋期间不可还原棋局</p>
	</div>
	<div id="container">
		<div id="top">
			<p class="p1">你的ID:<span id="userid"><?php echo $userid; ?></span></p>
			<p class="p2">当前对战:  <span id="buid">无</span> <strong>黑子</strong>  <span id="wuid">无</span> <strong>白子</strong><em id="alertinfo"></em></p>
		</div>
		<table>
		<?php for($i=0;$i<10;$i++){?>
			<tr>
			<?php for($j=0;$j<10;$j++){?>
				<td></td>
			<?php }?>
			</tr>
		<?php }?>

		</table>
		<div id="rightbar">
	
			<p><button id="check">查看邀请</button></p>
			<p><button id="start">开始下子</button></p>
			<p><button id="back">悔棋</button></p>
			<p><button id="clear">清局</button></p>


<!--			<p><button id="restore">还原</button>-->
<!--			第<input type="number" name="restore" value="1">局-->
<!--			</p>-->
		</div>
				
		<div id="user">
			<h2>在线用户<img src="img/reload.png"/></h2>
			<ul>
				<?php foreach($allUser as $value){ ?>
				<li <?php if($value==$userid){echo "class='red'";} ?>><span class="onlineuid"><?php echo $value; ?></span></span><span class="invite" onclick="invite(this)">邀请</span></li>
				<?php } ?>
			</ul>
		</div>
	</div>
<body>
</html>