<html><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>workerman-chat PHP聊天室 Websocket(HTLM5/Flash)+PHP多进程socket实时推送技术</title>
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/style.css" rel="stylesheet">
	
  <script type="text/javascript" src="/js/swfobject.js"></script>
  <script type="text/javascript" src="/js/web_socket.js"></script>
  <script type="text/javascript" src="/js/jquery.min.js"></script>


  <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    // 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
    WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    // 开启flash的websocket debug
    WEB_SOCKET_DEBUG = true;
    var ws, name, client_list={};

    // 连接服务端
    function connect() {
        // 与GatewayWorker建立websocket连接，域名和端口改为你实际的域名端口
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
                    $.post('./bind.php', {client_id: data.client_id}, function(data){}, 'json');
                    break;
                // 当mvc框架调用GatewayClient发消息时直接alert出来
                default :
                    alert(e.data);
            }
        };
    }

  </script>
</head>
<body onload="connect();">

</body>
</html>
