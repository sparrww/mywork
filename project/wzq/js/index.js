//定义一个10x10的二维数组,0表示棋格上没有子，-1表示有白子，1表示有黑子
window.qipan =new Array();
//定义一个一维数组，用来存储下棋的顺序和位置
window.order = new Array();
//当前棋子颜色
window.flag='black';
//标记棋局是否已经获胜,假定获胜
window.win = true;

//判断是否在还原期间，1表示是，0表示不是
window.restore=0;
//0表示黑方未处于监听状态
window.listener='0';



$(function(){

	listenerfuc('put0');
	
	//初始化qipan	
	for (var i = 0; i < 10 ; i++){
		qipan[i]=new Array();
		for (var j = 0; j < 10; j++){
			qipan[i][j] = 0;
		}
	}
	//开始下子事件
	$('#start').click(function(){
		//还原棋盘期间不能点击下棋
		if(restore) return;
		if($('#buid').html()=='无'){
			alert('先邀请一个用户和你游戏');
			return;
		}
		//下棋期间不可再点击下子
		if(!win){
			alert('请先下完该局');
			return;
		}
		//调用清局
		clear();
		//把黑子处于监听状态的信息储存
		if($('#userid').html()==$('#buid').html()){
			
			listenerfuc('put1');
		}
		
		//棋盘初始化
		order[0]=44;
		qipan[4][4]=1;
		flag='white';
		win=false;
		$('td').eq(44).addClass('black');
		
		$.ajax({
			type:'post',
			url:'online.php',
			data:{
				'a':'put',
				'qipan':'1',
				'order':'44',
				'flag':'white',
				'win':'0',
				'back':'0',
				'fromuid':$('#buid').html(),
			},
			success:function(text){
			},
			async:false,
		});


		
		// window.sit=setInterval(function(){
		// 	$.ajax({
		// 		type:'post',
		// 		url:'online.php',
		// 		data:'a=update&fromuid='+$('#buid').html(),
		// 		success:function(text){
		// 			var info = text.split('|');
		// 			var qipanStr=parseInt(info[0]);
		// 			var orderStr=parseInt(info[1]);
		// 			var flagStr=info[2];
		// 			var winStr=info[3];
		// 			var backStr=info[4];
		// 			var row=Math.floor(orderStr/10);
		// 			var col=orderStr%10;
		// 			flag=flagStr;
		// 			win=winStr=='1'?true:false;
		// 			if(backStr=='1'){//悔棋操作
		// 				if (qipan[row][col]!=0){//就是还有棋，把这个格子的棋去掉
		// 					qipan[row][col]=qipanStr;//0
		// 					order.pop();//移除
		// 					$('td').eq(orderStr).removeClass('black').removeClass('white');
		// 				}
		// 			}else if(backStr=='0'){
		// 				if (qipan[row][col]!=qipanStr){//表示棋盘第row行第col列上没有棋子
		//
		// 					qipan[row][col]=qipanStr;
		// 					order.push(orderStr);
		// 					if(qipanStr==1){
		// 						$('td').eq(orderStr).addClass('black');
		// 						/*
		// 						if(win){
		// 							clearInterval(sit);
		// 							alert('黑子赢');
		// 						}
		// 						*/
		// 					}else if(qipanStr==-1){
		// 						$('td').eq(orderStr).addClass('white');
		// 						/*
		// 						if(win){
		// 							clearInterval(sit);
		// 							alert('白子赢');
		// 						}
		// 						*/
		// 					}
		// 				}
		// 			}
        //
		// 			//如果获胜，则清除定时
		// 			if(win){
		// 				clearInterval(sit);
		// 				if(qipanStr==1){
		// 					$('#alertinfo').html('黑棋获胜');
		// 					//alert('黑棋获胜');
		// 				}else{
		// 					$('#alertinfo').html('白棋获胜');
		// 					//alert('白棋获胜');
		// 				}
		// 			}
		//
		// 		},
		// 		async:false,
		// 	});
		//
		// },1000);
		
	});

	
	
	
	
	//td点击事件
	$('td').click(function(){
		if(restore) return;//还原期间不能下子
		//如果已经有人获胜则不能再下子
		if (win){
			return;
		}
		//如果用户不是当前对战者，则不可下子
		if($('#userid').html() != $('#buid').html() && $('#userid').html()!=$('#wuid').html()){
			alert('请先观战，马上就到');
			return;
		}
		//如果当前对战者不是系统分配的颜色的棋子，则不能下棋
		if ($('#userid').html()==$('#buid').html() && flag !='black'){
			alert('等待白子...');
			return;
		}
		if ($('#userid').html()==$('#wuid').html() && flag !='white'){
			alert('等待黑子...');
			return;
		}

		//判断该格子是否可以有棋子
		if ($(this).hasClass('white') || $(this).hasClass('black')){
			alert('不能在此下棋');
			return;
		}
		//检查黑子是否处于监听状态
		if(order.length==1){//只有一颗棋子是才检查
			if($('#userid').html()==$('#wuid').html()){
				$.ajax({
					type:'post',
					url:'listener.php',
					data:'a=get&fromuid='+$('#buid').html(),
					success:function(text){
						listener=text;
					},
					async:false,
				});
				if(listener=='0'){
					alert('黑方未点击“开始下子”，请等待...');
					return false;
				}
			}
		}
		//用户点击的是哪个单元格
		var index = $('td').index(this);
		var row = Math.floor(index/10);
		var col = index%10;
		//判读应该下哪种颜色的棋子
		if (flag=='black'){
			$(this).addClass(flag);
			flag='white';
			//棋子下后给qipan相应的数组元素給-1（白）或1（黑）
			qipan[row][col] = 1;
		}else if (flag=='white'){
			$(this).addClass(flag);
			flag='black';
			qipan[row][col] = -1;
		}
		
		//记录下棋的顺序
		order.push(index);
		

		
		//判断输赢算法
		if(order.length>=9){//只有棋盘上的棋子总数大于9个，才判定输赢，减少遍历次数
			for (var i = 0; i < 10 ; i++){
				for (var j = 0; j < 10; j++){
		
					//第一种可以获胜的情况：横向相同颜色的棋子连续有5个
					var num1 =0;	//只有num的值等于5（黑子赢）或-5（白子赢）
					if (j <=5){		//防止qipan[i][j+4]越界
						num1 = qipan[i][j] + qipan[i][j+1] + qipan[i][j+2] + qipan[i][j+3] + qipan[i][j+4];
						if (num1==5){
							win=true;
							
							//alert('黑子赢');
							break;
						}else if (num1==-5){
							win=true;
					
							//alert('白子赢');
							break;
						}
					}
			
					//第二种情况：纵向相同颜色的棋子连续有5个
					var  num2 =0;
					if (i<=5){	//防止qipan[i+4][j]越界
						num2 = qipan[i][j] + qipan[i+1][j] + qipan[i+2][j] + qipan[i+3][j] + qipan[i+4][j];
						if (num2==5){
							win=true;
						
							//alert('黑子赢');
							break;
						}else if (num2==-5){
							win=true;
							
							//alert('白子赢');
							break;
						}
					}
					
					//第三种情况：斜向相同颜色的棋子连续有5个
					//正斜向
					var  num3 =0;//置0这步很重要
					if (i<=5 && j<=5){	//防止qipan[i+4][j+4]越界
						num3 = qipan[i][j] + qipan[i+1][j+1] + qipan[i+2][j+2] + qipan[i+3][j+3] + qipan[i+4][j+4];		
					}
					//反斜向
					var num4 =0;
					if (i<=5 && j>=4){	//防止qipan[i+4][j-4]越界
						num4 = qipan[i][j] + qipan[i+1][j-1] + qipan[i+2][j-2] + qipan[i+3][j-3] + qipan[i+4][j-4];		
					}
					if (num3==5 || num4==5 ){
						win=true;
		
						//alert('黑子赢');
						break;
					}else if (num3==-5 || num4==-5){
						win=true;
						
						//alert('白子赢');
						break
					}
					
				}
				//如果已经有人获胜，则跳出整个循环并记录该局的信息
				if(win){
					//需要记录qipan\order
					//var filename=$('#buid').html()+$('#wuid').html();//文件名
					var qipanStr = JSON.stringify(qipan);//转为json字符串	
					var orderStr = JSON.stringify(order);
					$.ajax({
						type:'post',
						url:'online.php',
						data:{
							'a':'store',
							//'filename':filename,
							'qipan':qipanStr,
							'order':orderStr,
							'fromuid':$('#buid').html(),
						},
						success:function(text){
							if (text=='1'){
								alert('记录保存失败');
							}
						},
						async:false,
						
					});
					break;
				}
				
			}
		}
		
		//记录order\qipan\flag\win到文件中去
		
		var winStr = win?'1':'0';
		$.ajax({
			type:'post',
			url:'online.php',
			data:{
				'order':index,
				'qipan':qipan[row][col],
				'win':winStr,
				'flag':flag,
				'back':'0',//表示这是一个添加棋子的操作，而不是悔棋
				'a':'put',
				'fromuid':$('#buid').html(),
			},
			async:false,
		});	
		
	});
	
	//悔棋算法
	$('#back').click(function(){//用户点击悔棋按钮，触发悔棋事件
		if ($('#buid').html()=='无') {
			alert('未加入对战，无棋可悔');
			return;
		}
		if(restore) return;//还原棋局期间不能悔棋
		if(win) return;//已经分出胜负的棋局不能悔棋
		if(order.length==0) return;//棋盘上没有棋子不能悔棋	
		//要悔棋子的坐标
		var axis=order[order.length-1];
		var row = Math.floor(axis/10);
		var col = axis%10;
		//判断用户悔的是否是自己的棋子
		if (qipan[row][col]==1 && $('#userid').html()==$('#wuid').html()){
			alert('不能悔对方的棋');
			return;
		}
		if (qipan[row][col]==-1 && $('#userid').html()==$('#buid').html()){
			alert('不能悔对方的棋');
			return;
		}
		//把悔棋单元格的值重设为0
		qipan[row][col]=0;
		//并把该单元格的棋子移除
		if (flag=='white'){
			$('td').eq(axis).removeClass('black');
			flag='black';
		}else if(flag=='black'){
			$('td').eq(axis).removeClass('white');
			flag='white';
		}
		//悔棋之后要把order最后一个元素（即悔的棋子）pop弹出
		order.pop();
		//把悔棋信息放入文件中
		var winStr = win?'1':'0';
		$.ajax({
			type:'post',
			url:'online.php',
			data:{
				'order':axis,//悔棋的坐标
				'qipan':qipan[row][col],
				'win':winStr,
				'flag':flag,
				'back':'1',//1表示是悔棋，0表示不是
				'a':'put',
				'fromuid':$('#buid').html(),
			},
			async:false,
		});	
	});
	
	
	//清局算法
	$('#clear').click(function(){
		if (!win) {
			alert('下棋期间不可清局');
			return;
		}
		clear();
		if (typeof(sit)!='undefined') clearInterval(sit);//关闭定时功能
		listenerfuc('put0');//同时存储监听状态
	});
	
	
	
	//还原棋局
	$('#restore').click(function(){//用户点击还原按钮，触发还原事件	
		//下棋期间不可还原棋局
		if(!win) return;
		alert('火狐可看出效果');
		var optval=$('input[name=restore]').val();//该变量用于表示第几局
		//var filename=$('#buid').html()+$('#wuid').html();//记录期间信息的文件名
		
		$.ajax({
			type:'post',
			url:'restore.php',
			data:{
				'optval':optval,
				//'filename':filename,
				'fromuid':$('#buid').html(),
			},
			success:function(text){
				if(text=='1'){
					alert('不存在该局');
				}else if(text=='2'){
					alert('还没有参加对战');
				}else{
					//返回第optval局的棋局信息，qipan和order是json字串，使用|隔开
					var data=text.split('|');
					qipan=JSON.parse(data[0]);
					order=JSON.parse(data[1]);
					//order为空则不能还原
					if (order.length==0) return;
					//判断是否在还原期间，1表示是，0表示不是
					restore=1;//还原期间
					//移除前一局的所有棋子
					$('td').removeClass('white').removeClass('black');	
					for (var i=0; i<order.length; i++){
						
						//延迟1秒,因为setTimeout是并发执行，实在没办法才使用ajax
						$.ajax({
							type:'post',
							url:'delay.php',
							async:false
						});
						
						//该棋子的坐标
						var row=Math.floor(order[i]/10);
						var col=order[i]%10;
						//该棋子的颜色
						var color='';
						if (qipan[row][col]==1){
							color='black';
						}else if(qipan[row][col]==-1){
							color='white';
						}
						$('td').eq(order[i]).addClass(color);
								
					}
					//还原期间结束
					restore=0;
				}
			},
			async:false,
		});
	});

	//查看邀请
	$('#check').click(function(){
		$.ajax({
			type:'post',
			url:'check.php',
			data:'uid='+$('#userid').html(),
			success:function(text){
				var info=text.split('|');
				if(info[0]=='1'){
					alert('等待对方接受邀请');
				}else if(info[0]=='2'){				
					alert('对方已接受邀请,点击“开始下子”开始游戏吧');
					$('#buid').html($('#userid').html());
					$('#wuid').html(info[1]);
				}else if(info[0]=='3'){
					//当用户点击查看邀请后
					var data=confirm('确认接受邀请吗？')? '2' :'1';
					$.ajax({
						type:'post',
						url:'confirm.php',
						data:
							{
								a:'put',
								d:data,
								uid:$('#userid').html(),
							},
						success:function(text){
							if(text=='1'){
								
							}else{
								$('#buid').html(text);
								$('#wuid').html($('#userid').html());
							}
						},
						async:false,
					});
				}else if(text=='4'){
					alert('没有邀请信息');
				}
				
			},
			async:false,
		});
	});
		
	//刷新显示在线用户	
	$('#user img').click(function(){
		$.ajax({
			type:'post',
			url:'user.php',
			success:function(text){
				var users=text.split('|');
				var li='';
				for(var i=0; i<users.length; i++){
					console.log(123)
					if(users[i]==$('#userid-bak').html()){
						li+='<li class="red"><span class="onlineuid">'+users[i]+'</span><span class="invite" onclick="invite(this)">邀请</span></li>';
					}else{
						li+='<li><span class="onlineuid">'+users[i]+'</span><span class="invite" onclick="invite(this)">邀请</span></li>';
					}
				}
				$('#user ul').html(li);
			},
			async:false,
		
		});
	});
	/*
	//手动模拟更新棋局
	$('#update').click(function(){
		$.ajax({
			type:'post',
			url:'online.php',
			data:'a=update',
			success:function(text){
				var info = text.split('|');
				//qipan=JSON.parse(info[0]);
				order.push(info[1]);
				flag=info[2];
				win= info[3]=='0' ? false :true;
				var row=Math.floor(info[1]/10);
				var col=info[1]%10;
				if (flag=='white'){
					$('td').eq(info[1]).addClass('black');
					qipan[row][col]=1;
				}else if(flag=='black'){
					$('td').eq(info[1]).addClass('white');
					qipan[row][col]=-1;
				}
				if(win){
					if (flag=='white'){
						alert('黑子获胜');
					}else if(flag=='black'){
						alert('白子获胜');
					}
				}
			}
		});
	});
		*/
		

});

//邀请函数
//_this表示点击邀请的DOM对象
function invite(_this){
	var index=$('.invite ').index(_this);
	var touid=$('.onlineuid').eq(index).html();//被邀请人的id
	var fromuid=$('#userid').html();//邀请人的id
	if(touid==fromuid){
		alert('不能邀请自己');
		return;
	}
	$.ajax({
		type:'post',
		url:'invite.php',
		data:{
			'touid':touid,
			'fromuid':fromuid
		},
		success:function(text){
			if(text=='1'){
				alert('你已经存在邀请信息或对方已被邀请');
			}else if(text=='2'){
				alert('邀请失败！');
			}else if(text=='3'){
				alert('邀请成功，等待对方确认');
			}else{
				alert(text);
			}
		},
		async:false,
	});
	
}

//清局函数
function clear(){
	for (var i = 0; i < 10 ; i++){
		for (var j = 0; j < 10; j++){
			qipan[i][j] = 0;
		}
	}
	$('td').removeClass('white').removeClass('black');
	//把起始棋子颜色设为黑色
	flag='black';
	//清空order
	order.splice(0,order.length);
	//提醒的获胜信息设置为空
	$('#alertinfo').html('');
}
		//设置黑方的监听状态
function listenerfuc(str){
	$.ajax({
		type:'post',
		url:'listener.php',
		data:{
			'a':str,
			'fromuid':$('#buid').html(),
		},
		success:function(text){

		},
		async:false,
	});
}

//删除用户
window.onbeforeunload=function(){
	$.ajax({
		type:'post',
		url:'delUser.php',
		data:'userid='+$('#userid').html(),
		async:false,
	});

	return '确定离开吗，亲？';
}

function online(text) {
	var info = text.split('|');
	var qipanStr=parseInt(info[0]);
	var orderStr=parseInt(info[1]);
	var flagStr=info[2];
	var winStr=info[3];
	var backStr=info[4];
	var row=Math.floor(orderStr/10);
	var col=orderStr%10;
	flag=flagStr;
	win=winStr=='1'?true:false;
	if(backStr=='1'){//悔棋操作
		if (qipan[row][col]!=0){//就是还有棋，把这个格子的棋去掉
			qipan[row][col]=qipanStr;//0
			order.pop();//移除
			$('td').eq(orderStr).removeClass('black').removeClass('white');
		}
	}else if(backStr=='0'){
		if (qipan[row][col]!=qipanStr){//表示棋盘第row行第col列上没有棋子

			qipan[row][col]=qipanStr;
			order.push(orderStr);
			if(qipanStr==1){
				$('td').eq(orderStr).addClass('black');
				/*
				 if(win){
				 clearInterval(sit);
				 alert('黑子赢');
				 }
				 */
			}else if(qipanStr==-1){
				$('td').eq(orderStr).addClass('white');
				/*
				 if(win){
				 clearInterval(sit);
				 alert('白子赢');
				 }
				 */
			}
		}
	}

	//如果获胜，则清除定时
	if(win){
		if(qipanStr==1){
			$('#alertinfo').html('黑棋获胜');
			//alert('黑棋获胜');
		}else{
			$('#alertinfo').html('白棋获胜');
			//alert('白棋获胜');
		}
	}
}

