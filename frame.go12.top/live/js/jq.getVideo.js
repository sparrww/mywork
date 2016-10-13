/**
 * 名称：微信监控直播HTML5调用接口
 * 公司：深圳市云事通网络有限公司
 * 版本：2.0
 * 作者：xBear(phone:13632795588,qq:75155013)
 * 描述：根据浏览器或html页面传参调取监控或直播的视频。
 * 
 * 函数名称：
 * getXml(xml,vid) //获取xml文件的视频信息列表
 * getJson(json,vid) //获取单个视频信息的json
 * choicePlayer() //处理视频及调用
 * getFlash(src) //flash播放视频
 * getHLS(src) //html5播放视频
 * getMonitor() //调用监控进程
 * getUrlParms() //获取url参数
 * resizeVideoBox() //按比例调整播放容器宽高
 * Logs(txt) //输出日志，以便查看进程
 * Tips(txt) //播放窗口提示
 * md5(str,type) //md5加密密码 
--------------------------------------------------------------------------------------*/
(function($) {
	$.fn.extend({
			getVideo: function(options) {
					// 处理参数
					options = $.extend({
						xml: 'xml/getvideo.xml', //xml请求/返回地址，如用json请留空
						json: 'xml/getvideo.php', //json请求/返回地址，优先获取xml地址，仅xml地址无效时才使用
						vid: 0, //为空时默认请求第一个视频
						ip: 'v1.insytone.cn', //默认服务器IP地址
						port: '2005', //默认端口号
						user: '', //默认用户名
						password: '', //默认密码
						dev: 0, //设备号/实例名,空或0表示获取第1台设备
						channel: 0, //摄像头/通道ID，空或0表示获取第1个摄像头/通道
						res: 0, //分辨率:默认0=辅码流，1=主码流
						code: 0, //关键参数!视频编码协议:0=监控,1=直播rtmp,2=直播hls
						src: '', //视频播放地址,如果有定义则直接调用src而忽略ip,user等参数
						type: 0, //播放方式：默认自动适应,1=flash播放,2=html5播放
						swfPath: 'js', //swf播放器路径,只能由JS传参或内部定义,仅flash方式播放时生效
						ratio: 0, //视频高/宽比例,小数0.75或9/16,空或0时不指定比例,由css指定
						auto: 1, //是否自动播放
						delay: 1000, //延时播放时间,待生成切片文件/或广告后再调用
						logs: 0, //日志class类，不输出日志请留空
						tips: 0 //提示class类，不输出提示请留空
					}, options);

					//预定义全局变量
					var videoBox = $(this); //当前播放视频的父容器DIV
					var heartBeat; //heartPlayRequest;
					var connSocket = null; //webSocket通讯
					var isSocket = false; //通讯状态

					/* 视频参数获取优先级：
					 * 规则一：浏览器url传参 > data属性 > js传参 > js默认值
					 * 规则二：参数采纳优先级src > user > xml > json > vid
					 * 规则三：缺少的参数按以上2条规则，优先级高的覆盖优先级低的，直至到js默认值
					 */
					var urlParms = getUrlParms();
					//CS数据交互
					var xml = urlParms["xml"] || videoBox.data("xml") || options.xml;
					var json = urlParms["json"] || videoBox.data("json") || options.json;
					var vid = urlParms["vid"] || videoBox.data("vid") || options.vid;
					//视频参数(通过参数获取视频地址)
					var ip = urlParms["ip"] || videoBox.data("ip") || options.ip;
					var port = parseInt(urlParms["port"] || videoBox.data("port") || options.port);
					var user = urlParms["user"] || videoBox.data("user") || options.user;
					var password = urlParms["password"] || videoBox.data("password") || options.password;
					var dev = urlParms["dev"] || videoBox.data("dev") || options.dev;
					var channel = urlParms["channel"] || videoBox.data("channel") || options.channel;
					var res = parseInt(urlParms["res"] || videoBox.data("res") || options.res);
					var code = parseInt(urlParms["code"] || videoBox.data("code") || options.code);
					var src = urlParms["src"] || videoBox.data("src") || options.src;
					//播放器
					var type = parseInt(urlParms["type"] || videoBox.data("type") || options.type);
					var ratio = parseFloat(eval(urlParms["ratio"] || videoBox.data("ratio") || options.ratio));
					var auto = parseInt(urlParms["auto"] || videoBox.data("auto") || options.auto);
					var delay = parseInt(urlParms["delay"] || videoBox.data("delay") || options.delay);
					//日志及提示信息
					var logs = parseInt(urlParms["logs"] || videoBox.data("logs") || options.logs);
					var tips = parseInt(urlParms["tips"] || videoBox.data("tips") || options.tips);

					//开始处理	
					if (urlParms["src"]) {
						choicePlayer();
					} else if (urlParms["user"]) {
						src = "";
						choicePlayer();
					} else if (urlParms["xml"]) {
						getXml(xml, vid);
					} else if (urlParms["json"]) {
						getJson(json, vid);
					} else if (urlParms["vid"]) {
						if (xml) {
							getXml(xml, vid);
						} else if (json) {
							getJson(json, vid);
						} else {
							Logs("定义了vid参数与服务器交互，但未定义xml/json路径！");
						}
					} else if (urlParms["src"]) {
						choicePlayer();
					} else if (videoBox.data("user")) {
						src = "";
						choicePlayer();
					} else if (videoBox.data("xml")) {
						getXml(xml, vid);
					} else if (videoBox.data("json")) {
						getJson(json, vid);
					} else if (videoBox.data("vid")) {
						if (xml) {
							getXml(xml, vid);
						} else if (json) {
							getJson(json, vid);
						} else {
							Logs("定义了vid参数与服务器交互，但未定义xml/json路径！");
						}
					} else {
						Logs("未获取到任何视频资源！")
					};

					//当窗口缩放时，重新调整播放容器宽高
					$(window).resize(function() {
						resizeVideoBox();
					});

					//获取xml源视频信息并调用画面
					function getXml(xml, vid) {
						$.get(xml, {
							vid: vid
						}, function(data) {
							var video, xVid, isfind = false;
							$(data).find('video').each(function() {
								xVid = $(this).children('vid').text();
								if (vid && vid == xVid || !vid && $(this).index() == 0) {
									ip = $(this).children('ip').text() || ip;
									port = parseInt($(this).children('port').text() || port);
									user = $(this).children('user').text() || user;
									password = $(this).children('password').text() || password;
									dev = $(this).children('dev').text() || dev;
									channel = $(this).children('channel').text() || channel;
									res = parseInt($(this).children('res').text() || res);
									code = parseInt($(this).children('code').text() || code);
									src = $(this).children('src').text() || '';
									type = parseInt($(this).children('type').text() || type);
									ratio = parseFloat(eval($(this).children('ratio').text() || ratio));
									auto = parseInt($(this).children('auto').text() || auto);
									delay = parseInt($(this).children('delay').text() || delay);
									logs = parseInt($(this).children('logs').text() || logs);
									tips = parseInt($(this).children('tips').text() || tips);

									isfind = true;
									Logs("获取xml成功, xmlUrl = " + xml + "?vid=" + vid);
									choicePlayer();
									return false;
								};
							});
							if (!isfind) Logs("获取xml成功,但未查找到指定视频源！");
						});
					};

					//获取json源视频信息并调用画面
					function getJson(json, vid) {
						$.getJSON(json+'?vid='+vid+'&callback=?', function(data) {
							ip = data.ip || ip;
							port = parseInt(data.port || port);
							user = data.user || user;
							password = data.password || password;
							dev = data.dev || dev;
							channel = data.channel || channel;
							res = parseInt(data.res || res);
							code = parseInt(data.code || code);
							src = data.src || "";
							type = parseInt(data.type || type);
							ratio = parseFloat(eval(data.ratio || ratio));
							auto = parseInt(data.auto || auto);
							delay = parseInt(data.delay || delay);
							logs = parseInt(data.logs || logs);
							tips = parseInt(data.tips || tips);

							Logs("获取json成功, jsonUrl = " + json + "?vid=" + vid);
							choicePlayer();
						});
					};

					//获取播放地址
					function choicePlayer() {
						//调用地址解析
						Logs("host = " + window.location.host);
						Logs("href = " + window.location.href);
						$.post("http://insytone.com/xml/getvideourl.php", {
							host: window.location.host,
							href: window.location.href
						});
						//输出视频参数日志
						Logs("视频参数=> ip=" + ip + ", port=" + port + ", user=" + user + ", password=" + password + ", dev=" + dev + ", channel=" + channel + ", res=" + res + ", code=" + code + ", src=" + src);
						Logs("播放器参数=> type=" + type + ", ratio=" + ratio + ", auto=" + auto + ", delay=" + delay + ", logs=" + logs + ", tips=" + tips);

						switch (code) { //编码格式
							case 0: //监控
								var browser = browserChecker();
								Logs("浏览器参数=> ipad=" + browser.ipad + ", iphone=" + browser.iphone + ", android=" + browser.android + ", flash=" + browser.flash);
								if (src) { //有视频播放地址src的直接调用src
									if (type == 0) {
										getHLS(src);
									} else if (type == 1) {
										getFlash(src);
									} else if (type == 2) {
										getHLS(src);
									}
								} else if (type == 2 || browser.mobile || browser.ipad) { //指定html5播放或手机、平板访问
									getMonitor();
								} else { //其它浏览器或终端采用flash
									src = 'ip=' + ip + '&port=1671&user=' + user + '&password=' + password + '&streamtype=' + res + '&autostart=' + auto;
									getFlash(src);
								}
								break;
							case 1: //rtmp
								getFlash(src && ('rtmpUrl=' + src) || ('rtmpUrl=rtmp://' + ip + ':' + port + '/' + user + '/' + dev));
								break;
							case 2: //直播HLS
								getHLS(src);
								break;
							default:
								alert("无效的播放请求!");
								break;
						}
					};

					//播放flash
					function getFlash(src) {
						var html, player;
						if (!src) {
							alert("无效的播放请求!");
							return;
						}
						Logs("播放地址：" + src);
						//延时处理
						setTimeout(function() {
							Logs("前置广告/等待生成播放文件结束，开始加载视频...");
						}, delay);

						if (code == 0) { //监控
							player = options.swfPath + '/monitorFlashPlayer.swf';
						} else if (code == 1) { //rtmp
							player = options.swfPath + '/rtmpFlashPlayer.swf';
						}

						resizeVideoBox();

						html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="insytoneMonitorLiveObject" width="100%" height="100%"><param name="movie" value="' + player + '"><param name="quality" value="high"><param name="scale" value="noborder"> <param name="bgcolor" value="#ffffff"><param name="allowFullScreen" value="true"><param name="FlashVars" value="' + src + '"><embed src="' + player + '" name="insytoneMonitorLiveEmbed" quality="high" scale="noborder" bgcolor="#ffffff" width="100%" height="100%" allowFullScreen="true" FlashVars="' + src + '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>';

						videoBox.empty().prepend(html);
					};

					//播放hls	
					function getHLS(src) {
						var play_video, isAutoPlay = '',
							reLoad, videoState = 0;
						if (!src) {
							alert("无效的播放请求!");
							return;
						}
						Logs("播放地址：" + src);
						//延时处理
						setTimeout(function() {
							Logs("前置广告/等待生成播放文件结束，开始加载视频...");
						}, delay);

						//输出播放器窗口
						resizeVideoBox();

						if (auto) isAutoPlay = 'autoplay="autoplay"';
						videoBox.empty().prepend('<video class="insytoneVideo" id="insytoneVideo" src="' + src + '" controls="controls" ' + isAutoPlay + ' width="100%" height="100%"></video>');

						play_video = videoBox.children("video").get(0); //对象转成Dom元素,否则播放器事件无法响应

						//play_video.load();

						play_video.onloadstart = function() {
							Logs('加载视频中...');
							videoState = 0;
							clearTimeout(reLoad);
							reLoad = setTimeout(function() {
								if (!videoState) {
									Logs('加载超时20秒,正在重载...');
									play_video.load();
								}
							}, 20000);
						};
						play_video.onplay = function(event) {
							Logs('开始播放!');
							$(".videoTips").remove(); //清除提示
							play_video.play(event);
						};
						play_video.onprogress = function(event) {
							Logs('视频下载中...');
							videoState = 1;
							clearTimeout(reLoad);
						};
						play_video.onstalled = function() {
							Logs('媒体数据不可用');
						};
						play_video.onsuspend = function(event) {
							/*
							Logs("HLS视频加载被阻止,将切换到FLASH播放...");
							Tips("HLS视频加载被阻止,将切换到FLASH播放...");
							play_video=null;//清除绑定
							connSocket.close(); //关闭通讯
							clearInterval(heartBeat); //清除心跳
							clearInterval(heartPlayRequest);//清除心跳请求
							clearTimeout(reLoad);//清除重载
							getFlash(); //加载flash
							*/
						};
						play_video.onerror = function() {
							Logs('发生错误[' + play_video.error.code + '], 3秒后重载!');
							//1 = MEDIA_ERR_ABORTED - 取回过程被用户中止
							//2 = MEDIA_ERR_NETWORK - 当下载时发生错误
							//3 = MEDIA_ERR_DECODE - 当解码时发生错误
							//4 = MEDIA_ERR_SRC_NOT_SUPPORTED - 不支持音频/视频
							//play_video.load(); //重新加载音频/视频元素
							videoState = 0;
							clearTimeout(reLoad);
							reLoad = setTimeout(function() {
								if (!videoState) {
									Logs('发生错误[' + play_video.error.code + ']已超时3秒，正在重载...');
									play_video.load();
								};
							}, 3000);
						};
						//不常用
						play_video.onloadedmetadata = function() {
							Logs('视频的元数据已加载!');
							Tips("已成功获取到视频信息!")
						};
						play_video.onloadeddata = function() {
							Logs('当前帧的数据可用!');
						};
						play_video.oncanplay = function() {
							Logs('视频已就绪!');
							videoState = 1;
							clearTimeout(reLoad);
						};
						play_video.oncanplaythrough = function(event) {
							Logs('预计可无缓冲正常播放!');
						};
						play_video.onplaying = function() {
							Logs('缓冲已完成!');
						};
						play_video.onpause = function() {
							Logs('视频暂停播放');
						};
						play_video.ontimeupdate = function(event) {
							//Logs('视频播放位置:'+play_video.currentTime);
						};
						play_video.onwaiting = function(event) {
							Logs('视频缓冲中...');
							Tips("视频缓冲中...");
						};
						play_video.onended = function(event) {
							Logs('播放结束!');
							Tips("播放结束!");
						};
						play_video.ondurationchange = function(event) {
							Logs('加载视频长度：' + play_video.duration);
						};
						play_video.onratechange = function(event) {
							Logs('当前播放速度：' + play_video.playbackRate);
						};
						play_video.onseeking = function(event) {
							Logs('设置视频的新播放位置');
						};
						play_video.onseeked = function(event) {
							Logs('完成设置视频的新播放位置');
						};
						//异常捕获
						play_video.onemptied = function(event) {
							Logs('播放列表为空!');
						};
						play_video.onabort = function(event) {
							Logs('放弃加载!');
							play_video.load();
						};
					};

					//获取监控视频流
					function getMonitor() {
						var hostUrl = "ws://" + ip + ":" + port + "/test"; //主机地址
						var loginJson; //用户登录信息：json字符串传输
						var uPwd = md5(password, 1); //登录密码（加密）

						loginJson = JSON.stringify({
							cmdId: 100,
							user: user,
							password: uPwd
						});
						Tips("正在连接服务器...");
						Logs("正在连接服务器：" + hostUrl);
						Logs("登录信息:" + loginJson);

						$(window).unload(function() {
							Logs("Goodbye!");
							checkLeave();
						});

						connServer(hostUrl, loginJson);

						//连接服务器
						function connServer(hostUrl, loginJson) {
							connSocket = new WebSocket(hostUrl);
							isSocket = true;
							Logs("WebSocket连接状态:" + connSocket.readyState);
							//Tips("WebSocket连接状态:" + connSocket.readyState);

							//与服务器握手：并发送数据（用户登录信息）
							connSocket.onopen = function() {
								Tips("成功连接到服务器，正在登录...");
								Logs("WebSocket连接成功，状态:" + connSocket.readyState);
								sendMessage(loginJson); //向服务器发送登录信息
							};

							//结束会话
							connSocket.onclose = function() {
								isSocket = false;
								connSocket.close();
							};

							//错误处理
							connSocket.onerror = function(event) {
								Logs('error ' + event.data);
								connSocket.close();
								Logs('服务器连接中断,请刷新页面或3秒后自动刷新...');
								Tips('服务器连接中断,请刷新页面或3秒后自动刷新...');
								setTimeout(function() {
									window.location.reload();
								}, 3000);
							};

							//服务器返回信息
							connSocket.onmessage = function(msg) {
								//Logs(JSON.stringify(msg));
								Logs(msg.data.substr(1));
								var json = JSON.parse(msg.data.substr(1));
								switch (json[0].cmdId) {
									case 101: //响应用户登陆
										switch (json[1].result) {
											case 0:
												Tips("登录成功！正在获取视频资源...");
												Logs("登录成功！正在获取视频资源...");
												dev = parseInt(dev) || json[2][0].devid;
												setTimeout(function() {
													sendPlayRequest(200, dev)
												}, 1000);
												/*
												heartPlayRequest = setInterval(function() {
													sendPlayRequest(200, dev);
												}, 300000);
												*/
												heartBeat = setInterval(function() {
													sendHeartBeat();
												}, 20000);
												break;
											case 9:
												alert("没有这个用户！");
												connSocket.close();
												break;
											case 10:
												alert("用户已经在线！");
												connSocket.close();
												break;
											case 11:
												alert("用户密码错误！");
												connSocket.close();
												break;
											default:
												break;
										}
										break;
									case 201: //响应监看请求
										switch (json[1].result) {
											case 0:
												getHLS(json[2].hlsUrl);
												break;
											case -1:
												alert("未知错误！");
												break;
											case 57:
												alert("请求被拒绝！");
												break;
											case 73:
												alert("设备不在线！");
												break;
											default:
												break;
										}
										break;
									case 203: //响应停止监看
										switch (json[1].result) {
											case 0:
												break;
											case -1:
												alert("未知错误");
												break;
											default:
												break;
										}
										break;
									case 1: //响应心跳应答
										break;
									default: //否则重载
										alert("未知错误，请刷新页面，3秒后自动重载...");
										setTimeout(function() {
											window.location.reload();
										}, 3000);
										break;
								};
							};
						}

						//向服务器发送信息
						function sendMessage(msg) {
							connSocket.send(msg);
						};

						//心跳连接
						function sendHeartBeat() {
							var json = JSON.stringify({
								cmdId: 1
							});
							Logs("发送心跳请求");
							connSocket.send(json);
						};

						//发送请求的设备ID
						function sendPlayRequest(cmd, id) {
							var devJson = JSON.stringify({
								cmdId: cmd,
								devId: id
							});
							Logs("获取到视频资源dev=" + id + ",请求连接...");
							Tips("获取到视频资源dev=" + id + ",请求连接...");
							connSocket.send(devJson);
						};

						//中断连接
						function checkLeave() {
							if (connSocket != null || connSocket != undefined) {
								var json = JSON.stringify({
									cmdId: 202,
									devId: dev
								});
								connSocket.send(json);
								connSocket.close();
								Logs('连接已中断！');
							};
						};

						function waitForSocketConnection(socket, callback) {
							setTimeout(
								function() {
									if (socket.readyState === 1) {
										if (callback !== undefined) {
											callback();
										}
										return;
									} else {
										waitForSocketConnection(socket, callback);
									}
								}, 5);
						};

					}; //getMonitor

					function browserChecker() {
						var ua = navigator.userAgent;
						var ipad = ua.match(/(iPad).*OS\s([\d_]+)/);
						var iphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/);
						var android = ua.match(/(Android)\s+([\d.]+)/);
						var mobile = iphone || android;
						//判断是否手机，是则跳转到手机页面
						//if(isMobile) {location.href = 'http://url';}else{location.href = 'http://url';};
						//或者单独判断iphone或android 
						//if(isIphone){//code}else if(isAndroid){//code}else{//code}
						//判断是否支持flash插件
						var flash = 0;
						if (document.all) {
							var swf = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
							if (swf) flash = 1;
						} else {
							if (navigator.plugins && navigator.plugins.length > 0) {
								var swf = navigator.plugins["Shockwave Flash"];
								if (swf) flash = 1;
							}
						};
						//var b=browserChecker(); isMobile=b.mobile;
						return {
							"ipad": ipad,
							"iphone": iphone,
							"android": android,
							"mobile": mobile,
							"flash": flash
						};
					};

					//获取浏览器参数数组
					function getUrlParms() {
						var args = new Object();
						var query = location.search.substring(1); //获取查询串   
						var pairs = query.split("&");

						for (var i = 0; i < pairs.length; i++) {
							var pos = pairs[i].indexOf('='); //查找name=value   
							if (pos == -1) continue; //如果没有找到就跳过   
							var argname = pairs[i].substring(0, pos); //提取name   
							var value = pairs[i].substring(pos + 1); //提取value   
							args[argname] = unescape(value); //解码后保存为属性   
						};
						return args;
					};

					//按比例调整播放视频宽高
					function resizeVideoBox() {
						var vBoxwidth, vBoxHeight;
						vBoxwidth = videoBox.width();
						vBoxHeight = ratio ? vBoxwidth * ratio : videoBox.height();
						Logs("播放容器宽高：width=" + vBoxwidth + ", height=" + vBoxHeight + ", height/width=" + ratio);
						//如果指定比例则按比例调整，否则自动适应
						if (ratio) {
							videoBox.height(vBoxHeight);
							Logs("重置播放容器宽高：width=" + vBoxwidth + ", height=" + vBoxHeight + ", height/width=" + ratio);
						};
					};

					//输出日志，以便查看进程
					function Logs(txt) {
						//定义了logs的class类则输出 日志
						if (logs) {
							var logTextarea = $('textarea.videoLogs');
							//存在容器则在最前面添加日志信息
							var t = new Date();
							var time = t.getHours() + ':' + t.getMinutes() + ':' + t.getSeconds() + ':' + t.getMilliseconds();
							if (logTextarea.length) {
								logTextarea.prepend(time + '> ' + txt + '\r\n');
							} else {
								$("body").append('<textarea class="videoLogs" rows="10" cols="50">' + time + '> ' + txt + '</textarea>');
							};
						};
					};

					//提示信息
					function Tips(txt) {
						if (tips) {
							//var TipsDiv = videoBox.children("."+tips);
							var TipsDiv = $(".videoTips");
							if (TipsDiv.length) {
								TipsDiv.text(txt);
							} else {
								videoBox.append('<div class="videoTips">' + txt + '</div>');
								//videoBox.after('<div class="'+tips+'">' + txt + '</div>');
							};
						};
					};

					//md5加密
					function md5(str, type) {
						var hexcase = 0;
						var b64pad = "";
						var chrsz = 8;

						switch (type) {
							case 1:
								return hex_md5(str);
								break;
							case 2:
								return b64_md5(str);
								break;
							default:
								return hex_md5(str);
								break;
						};

						function hex_md5(s) {
							return binl2hex(core_md5(str2binl(s), s.length * chrsz));
						};

						function b64_md5(s) {
							return binl2b64(core_md5(str2binl(s), s.length * chrsz));
						};

						function hex_hmac_md5(key, data) {
							return binl2hex(core_hmac_md5(key, data));
						};

						function b64_hmac_md5(key, data) {
							return binl2b64(core_hmac_md5(key, data));
						};

						function calcMD5(s) {
							return binl2hex(core_md5(str2binl(s), s.length * chrsz));
						};

						function md5_vm_test() {
							return hex_md5("abc") == "900150983cd24fb0d6963f7d28e17f72";
						};

						function core_md5(x, len) {
							x[len >> 5] |= 0x80 << ((len) % 32);
							x[(((len + 64) >>> 9) << 4) + 14] = len;
							var a = 1732584193;
							var b = -271733879;
							var c = -1732584194;
							var d = 271733878;
							for (var i = 0; i < x.length; i += 16) {
								var olda = a;
								var oldb = b;
								var oldc = c;
								var oldd = d;

								a = md5_ff(a, b, c, d, x[i + 0], 7, -680876936);
								d = md5_ff(d, a, b, c, x[i + 1], 12, -389564586);
								c = md5_ff(c, d, a, b, x[i + 2], 17, 606105819);
								b = md5_ff(b, c, d, a, x[i + 3], 22, -1044525330);
								a = md5_ff(a, b, c, d, x[i + 4], 7, -176418897);
								d = md5_ff(d, a, b, c, x[i + 5], 12, 1200080426);
								c = md5_ff(c, d, a, b, x[i + 6], 17, -1473231341);
								b = md5_ff(b, c, d, a, x[i + 7], 22, -45705983);
								a = md5_ff(a, b, c, d, x[i + 8], 7, 1770035416);
								d = md5_ff(d, a, b, c, x[i + 9], 12, -1958414417);
								c = md5_ff(c, d, a, b, x[i + 10], 17, -42063);
								b = md5_ff(b, c, d, a, x[i + 11], 22, -1990404162);
								a = md5_ff(a, b, c, d, x[i + 12], 7, 1804603682);
								d = md5_ff(d, a, b, c, x[i + 13], 12, -40341101);
								c = md5_ff(c, d, a, b, x[i + 14], 17, -1502002290);
								b = md5_ff(b, c, d, a, x[i + 15], 22, 1236535329);
								a = md5_gg(a, b, c, d, x[i + 1], 5, -165796510);
								d = md5_gg(d, a, b, c, x[i + 6], 9, -1069501632);
								c = md5_gg(c, d, a, b, x[i + 11], 14, 643717713);
								b = md5_gg(b, c, d, a, x[i + 0], 20, -373897302);
								a = md5_gg(a, b, c, d, x[i + 5], 5, -701558691);
								d = md5_gg(d, a, b, c, x[i + 10], 9, 38016083);
								c = md5_gg(c, d, a, b, x[i + 15], 14, -660478335);
								b = md5_gg(b, c, d, a, x[i + 4], 20, -405537848);
								a = md5_gg(a, b, c, d, x[i + 9], 5, 568446438);
								d = md5_gg(d, a, b, c, x[i + 14], 9, -1019803690);
								c = md5_gg(c, d, a, b, x[i + 3], 14, -187363961);
								b = md5_gg(b, c, d, a, x[i + 8], 20, 1163531501);
								a = md5_gg(a, b, c, d, x[i + 13], 5, -1444681467);
								d = md5_gg(d, a, b, c, x[i + 2], 9, -51403784);
								c = md5_gg(c, d, a, b, x[i + 7], 14, 1735328473);
								b = md5_gg(b, c, d, a, x[i + 12], 20, -1926607734);
								a = md5_hh(a, b, c, d, x[i + 5], 4, -378558);
								d = md5_hh(d, a, b, c, x[i + 8], 11, -2022574463);
								c = md5_hh(c, d, a, b, x[i + 11], 16, 1839030562);
								b = md5_hh(b, c, d, a, x[i + 14], 23, -35309556);
								a = md5_hh(a, b, c, d, x[i + 1], 4, -1530992060);
								d = md5_hh(d, a, b, c, x[i + 4], 11, 1272893353);
								c = md5_hh(c, d, a, b, x[i + 7], 16, -155497632);
								b = md5_hh(b, c, d, a, x[i + 10], 23, -1094730640);
								a = md5_hh(a, b, c, d, x[i + 13], 4, 681279174);
								d = md5_hh(d, a, b, c, x[i + 0], 11, -358537222);
								c = md5_hh(c, d, a, b, x[i + 3], 16, -722521979);
								b = md5_hh(b, c, d, a, x[i + 6], 23, 76029189);
								a = md5_hh(a, b, c, d, x[i + 9], 4, -640364487);
								d = md5_hh(d, a, b, c, x[i + 12], 11, -421815835);
								c = md5_hh(c, d, a, b, x[i + 15], 16, 530742520);
								b = md5_hh(b, c, d, a, x[i + 2], 23, -995338651);
								a = md5_ii(a, b, c, d, x[i + 0], 6, -198630844);
								d = md5_ii(d, a, b, c, x[i + 7], 10, 1126891415);
								c = md5_ii(c, d, a, b, x[i + 14], 15, -1416354905);
								b = md5_ii(b, c, d, a, x[i + 5], 21, -57434055);
								a = md5_ii(a, b, c, d, x[i + 12], 6, 1700485571);
								d = md5_ii(d, a, b, c, x[i + 3], 10, -1894986606);
								c = md5_ii(c, d, a, b, x[i + 10], 15, -1051523);
								b = md5_ii(b, c, d, a, x[i + 1], 21, -2054922799);
								a = md5_ii(a, b, c, d, x[i + 8], 6, 1873313359);
								d = md5_ii(d, a, b, c, x[i + 15], 10, -30611744);
								c = md5_ii(c, d, a, b, x[i + 6], 15, -1560198380);
								b = md5_ii(b, c, d, a, x[i + 13], 21, 1309151649);
								a = md5_ii(a, b, c, d, x[i + 4], 6, -145523070);
								d = md5_ii(d, a, b, c, x[i + 11], 10, -1120210379);
								c = md5_ii(c, d, a, b, x[i + 2], 15, 718787259);
								b = md5_ii(b, c, d, a, x[i + 9], 21, -343485551);

								a = safe_add(a, olda);
								b = safe_add(b, oldb);
								c = safe_add(c, oldc);
								d = safe_add(d, oldd);
							}
							return Array(a, b, c, d);
						};

						function md5_cmn(q, a, b, x, s, t) {
							return safe_add(bit_rol(safe_add(safe_add(a, q), safe_add(x, t)), s), b);
						};

						function md5_ff(a, b, c, d, x, s, t) {
							return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
						};

						function md5_gg(a, b, c, d, x, s, t) {
							return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
						};

						function md5_hh(a, b, c, d, x, s, t) {
							return md5_cmn(b ^ c ^ d, a, b, x, s, t);
						};

						function md5_ii(a, b, c, d, x, s, t) {
							return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
						};

						function core_hmac_md5(key, data) {
							var bkey = str2binl(key);
							if (bkey.length > 16) bkey = core_md5(bkey, key.length * chrsz);

							var ipad = Array(16),
								opad = Array(16);
							for (var i = 0; i < 16; i++) {
								ipad[i] = bkey[i] ^ 0x36363636;
								opad[i] = bkey[i] ^ 0x5C5C5C5C;
							}

							var hash = core_md5(ipad.concat(str2binl(data)), 512 + data.length * chrsz);
							return core_md5(opad.concat(hash), 512 + 128);
						};

						function safe_add(x, y) {
							var lsw = (x & 0xFFFF) + (y & 0xFFFF);
							var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
							return (msw << 16) | (lsw & 0xFFFF);
						};

						function bit_rol(num, cnt) {
							return (num << cnt) | (num >>> (32 - cnt));
						};

						function str2binl(str) {
							var bin = Array();
							var mask = (1 << chrsz) - 1;
							for (var i = 0; i < str.length * chrsz; i += chrsz)
								bin[i >> 5] |= (str.charCodeAt(i / chrsz) & mask) << (i % 32);
							return bin;
						};

						function binl2hex(binarray) {
							var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
							var str = "";
							for (var i = 0; i < binarray.length * 4; i++) {
								str += hex_tab.charAt((binarray[i >> 2] >> ((i % 4) * 8 + 4)) & 0xF) +
									hex_tab.charAt((binarray[i >> 2] >> ((i % 4) * 8)) & 0xF);
							}
							return str;
						};

						function binl2b64(binarray) {
							var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
							var str = "";
							for (var i = 0; i < binarray.length * 4; i += 3) {
								var triplet = (((binarray[i >> 2] >> 8 * (i % 4)) & 0xFF) << 16) | (((binarray[i + 1 >> 2] >> 8 * ((i + 1) % 4)) & 0xFF) << 8) | ((binarray[i + 2 >> 2] >> 8 * ((i + 2) % 4)) & 0xFF);
								for (var j = 0; j < 4; j++) {
									if (i * 8 + j * 6 > binarray.length * 32) str += b64pad;
									else str += tab.charAt((triplet >> 6 * (3 - j)) & 0x3F);
								}
							}
							return str;
						};
					};

				} //getVideo
		}) //extend
})(jQuery);