<?php
// 函数库

defined('TPL_INCLUDE') or die('Restricted access');


/**
 * 文件载入
 *
 * @param  string $name	文件名称
 * @param  array  $ext	文件后缀
 * @return bool
 */
function import($name, $ext = '.php') {
    static $_loads = array();

    //$path = (substr($name, 0, 3) == 'App' ? APP_PATH : SYSTEM_PATH).'/';

    $path = SYSTEM_PATH.'/';

    //$name = str_replace('App.', '', $name);
    $name = str_replace('.', '/', $name);
    $file = $path . $name . $ext;

    if(isset($_loads[$file])) {       //如果已经载入过直接返回
        return true;
    }
    if(strpos($file, '*') > 1){       //如果有*号存在, 代表载入指定目录下的所有
        $files = glob($file);
        $len   = count($files);

        for($i = 0; $i < $len; $i++) {

            if(file_exists($files[$i])) {
                include_once($files[$i]);
                $_loads[$file] = true;
            }
        }
        return true;
    }elseif(file_exists($file)) {
        include_once($file);
        $_loads[$file] = true;
        return true;
    }
    return false;
}

function create_url($op='',$arr=[],$name='',$act=''){
    global $_W,$_GPC;
    $op = $op?$op:$_GPC['op'];
    $name = $name?$name:$_GPC['name'];
    $act = $act?$act:$_GPC['act'];

    $queryString = '';

    if($arr){
        $queryString = '&'.http_build_query($arr,'','&');
    }

    return 'index.php?act='.$act . '&name='.$name. '&op='.$op.$queryString;
}

/**
 * @method 多维数组转字符串
 * @param type $array
 * @return type $srting
 * @author yanhuixian
 */
function arrayToString($arr) {
    if (is_array($arr)){
        return implode(',', array_map('arrayToString', $arr));
    }
    return $arr;
}

/**
 * 二维数组转一维数组
 *
 * @param   type:array  $array  数组
 * @return  array
 */
function array_multitosingle( $array ){

    static $result_array = array();

    foreach( $array as $key => $value )
    {
        if( is_array( $value ) )
        {
            $result_array = array_merge( $result_array, $value);
        }
    }
    return $result_array;
}

/**
 * 读取配置文件
 *
 * @param   type:array  $array  数组
 * @return  array
 */
function getconfig($config){


    $array=include_once CONFIG_PATH.'/'.$config;

    return $array;
}

//--------------------------------------------------------
//	文件目录函数
//--------------------------------------------------------
/**
 * 批量创建目录
 *
 * @access  public
 * @param	string $path   文件夹路径
 * @param	int    $mode   权限
 * @return	bool
 */
function mkdirs($path, $mode = 0777){
    if (!is_dir($path)) {
        mkdirs(dirname($path), $mode);
        $error_level = error_reporting(0);
        $result      = mkdir($path, $mode);
        error_reporting($error_level);
        return $result;
    }
    return true;
}

/**
 * 删除文件夹
 *
 * @access  public
 * @param	string $path		要删除的文件夹路径
 * @return	bool
 */
function rmdirs($path){
    $error_level = error_reporting(0);
    if ($dh = opendir($path)) {
        while (false !== ($file=readdir($dh))) {
            if ($file != '.' && $file != '..') {
                $file_path = $path.'/'.$file;
                is_dir($file_path) ? rmdirs($file_path) : unlink($file_path);
            }
        }
        closedir($dh);
    }
    $result = rmdir($path);
    error_reporting($error_level);
    return $result;
}


/**
 * 写入文件
 *
 * @access  public
 * @param	string $files		文件名
 * @return	bool
 */
function fileWrite($content,$files,$path){
    mkdirs($path);

    $fp = fopen($path.'/'.$files, 'a+');
    $re=fputs($fp, $content);
    fclose($fp);
    if($re){
        return true;
    }else{
        return false;
    }
}


/**
 * 读取目录列表
 *
 * @access  public
 * @return	bool
 */
function getDir($dir) {
    $dirArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件

            if ($file != "." && $file != ".."&&!strpos($file,".")) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //关闭句柄

        closedir ( $handle );
    }
    return $dirArray;
}



/**
 * 读取文件列表
 *
 * @param	type:dir  $dir
 * @return	$array
 */
function getFile($dir) {
    $fileArray[]=NULL;

    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while( false !== ($file = readdir ( $handle )) ) {


            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&strpos($file,".")) {
                $fileArray[$i]=$file;
                if($i==100){
                    break;
                }
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $fileArray;

}


/**
 * XML转ARRAY
 *
 * @param  type:xml  $xml
 * @return $array/Xml
 */
function xml_to_array( $xml )
{
    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches))
    {
        $count = count($matches[0]);
        $arr = array();
        for($i = 0; $i < $count; $i++)
        {
            $key= $matches[1][$i];
            $val = xml_to_array( $matches[2][$i] );  // 递归
            if(array_key_exists($key, $arr))
            {
                if(is_array($arr[$key]))
                {
                    if(!array_key_exists(0,$arr[$key]))
                    {
                        $arr[$key] = array($arr[$key]);
                    }
                }else{
                    $arr[$key] = array($arr[$key]);
                }
                $arr[$key][] = $val;
            }else{
                $arr[$key] = $val;
            }
        }
        return $arr;
    }else{
        return $xml;
    }
}

/**
 * 替换文件路径以网站根目录开始，防止暴露文件的真实地址
 *
 * @param   type:path  $path
 * @return  type:path  返回一个相对当前站点的文件路径
 */
function replpath($path){
    $root_path = str_replace(DIRECTORY_SEPARATOR, '/', ROOT_PATH);
    $src_path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
    return str_replace($root_path, '', $src_path);
}


/**
 * 转义引号字符串
 * 支持单个字符与数组
 *
 * @param string or array $var
 * @return string or array
 *             返回转义后的字符串或是数组
 */
function istripslashes($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[stripslashes($key)] = istripslashes($value);
        }
    } else {
        $var = stripslashes($var);
    }
    return $var;
}

/**
 * 转义字符串的HTML
 * @param string or array $var
 * @return string or array
 *             返回转义后的字符串或是数组
 */
function ihtmlspecialchars($var)
{
    if (is_array($var)) {
        foreach ($var as $key => $value) {
            $var[htmlspecialchars($key)] = ihtmlspecialchars($value);
        }
    } else {
        $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
    }
    return $var;
}


/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else
        return $output;
}

/**
 * 生成分页数据
 * @param int $currentPage 当前页码
 * @param int $totalCount 总记录数
 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
 * @param int $pageSize 分页大小
 * @return string 分页HTML
 */
function pagination($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4,'isajax'=>0, 'ajaxcallback' => '')) {
    global $_W;
    $tcount = $tcount<=0 ? 0 : $tcount;
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    if($context['ajaxcallback']) {
        $context['isajax'] = true;
    }

    $pdata['tcount'] = $tcount;
    $pdata['tpage'] = ceil($tcount / $psize);
    /*if($pdata['tpage'] <= 1) {
        return '';
    }*/
    $cindex = $pindex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    if($context['isajax']) {
        if(!$url) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
    } else {
        if($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }

    $html = '<div class="pagination pagination-centered"><ul class="pagination pagination-centered">';
    $html .= '<li title="总条数/总页数"><a>'.$tcount.'/'.$pdata['tpage'].'</a></li>';
    if($pdata['cindex'] > 1) {
        $html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
        $html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
    }
    //页码算法：前5后4，不足10位补齐
    if(!$context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if(!$context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }

    if($context['after'] != 0 && $context['before'] != 0) {
        $range = array();
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        if($pdata['tpage']>1){
            for ($i = $range['start']; $i <= $range['end']; $i++) {
                if($context['isajax']) {
                    $aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
                } else {
                    if($url) {
                        $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                    } else {
                        $_GET['page'] = $i;
                        $aa = 'href="?' . http_build_query($_GET) . '"';
                    }
                }
                $html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
            }
        }
    }

    if($pdata['cindex'] < $pdata['tpage']) {
        $html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
        $html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
    }
    $html .= '</ul></div>';
    return $html;
}

function message($msg,$type='error'){
    global $_W;
    $type = in_array($type, array('success', 'error')) ? $type : 'error';
    if($_W['isajax']){
        $vars['type'] = $type;
        $vars['message'] = '';
        if (is_array($msg)) {
            $vars = array_merge($vars, $msg);
        } else {
            $vars['message'] = $msg;
        }
        exit(json_encode($vars));
    }else{
        exit($msg);
    }
}


/**
 * @param string $msg
 * @param string $data
 */
function json_dump($msg='',$data=''){
    if(!empty($msg)){
        echo json_encode(['error'=>1,'msg'=>$msg,'data'=>$data],JSON_UNESCAPED_UNICODE);die;
    }else{
        echo json_encode(['error'=>0,'data'=>$data],JSON_UNESCAPED_UNICODE);die;
    }
}

/**
 * php 接收流文件
 *
 * @param  type:file  $file 接收后保存的文件名
 * @return boolean
 */
function receiveStreamFile($receiveFile){

    $streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

    if(empty($streamData)){
        $streamData = file_get_contents('php://input');
    }

    if($streamData!=''){
        $ret = file_put_contents($receiveFile, $streamData, true);
    }else{
        $ret = false;
    }

    return $ret;

}

/*
 * 导出XLS文件
 *
 * @param  type:array	$data_ht  标题
 * @param  type:array	$data 	  内容
 * @param  type:String  $file_name 文件名
 */
function outputXlsHeader($data_ht,$data,$file_name = 'export'){
    header('Content-Type: text/xls');
    header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
    $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
    header('Content-Disposition: attachment;filename="' .$str . '.xls"');
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');

    $table_data = '<table border="1">';
    $table_data .= '<tr>';
    foreach ($data_ht as $title){
        $title = mb_convert_encoding($title, 'gbk', 'utf-8');
        $table_data .= '<th>' . $title . '</th>';
    }
    $table_data .= '</tr>';
    foreach ($data as $line){

        $table_data .= '<tr>';
        foreach ($line as $key => &$item){

            $item = mb_convert_encoding($item, 'gbk', 'utf-8');
            $table_data .= '<td>' . $item . '</td>';
        }
        $table_data .= '</tr>';
    }
    $table_data .='</table>';
    echo $table_data;
    die();
}

/**
 * 匹配图片路径
 */
function matchPic($content){
    $content = stripslashes(htmlspecialchars_decode($content));
    preg_match_all('/<img([ ]+)src="([^\"]+)"/i',$content,$matches);
    return $matches[2];
}


function iCurl($url,$curlPost='')
{

    $timeout = 3;

    $cip = '148.126.58.'.mt_rand(0,254);
    $xip = '148.126.47.'.mt_rand(0,254);
    $header = array(
        'CLIENT-IP:'.$cip,
        'X-FORWARDED-FOR:'.$xip,
    );

    $binfo =array('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; InfoPath.2; AskTbPTV/5.17.0.25589; Alexa Toolbar)','Mozilla/5.0 (Windows NT 5.1; rv:22.0) Gecko/20100101 Firefox/22.0','Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET4.0C; Alexa Toolbar)','Mozilla/4.0(compatible; MSIE 6.0; Windows NT 5.1; SV1)');

    $u = $binfo[mt_rand(0,2)];


    $ch = curl_init();

    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_REFERER, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_USERAGENT, $u);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents ;

}

function setLog($val){
    if(is_array($val) || is_object($val)){
        $str = var_export($val, true);
    }else{
        $str = $val;
    }
    //unlink(IA_ROOT.'/mylog.txt');
    file_put_contents(ROOT_PATH.'/mylog.txt', $str."\r\n======================================\r\n", FILE_APPEND);
}

/**
 * 中文转数字
 * @param String $var 需要解析的中文数
 * @param Int $start 初始值
 * @return int
 */
function CnToInt($var, $start = 0) {
    if (is_numeric($var)) {
        return $var;
    }
    if (intval($var) === 0) {
        $splits = array('亿' => 100000000, '万' => 10000);
        $chars = array('万' => 10000, '千' => 1000, '百' => 100, '十' => 10, '一' => 1, '零' => 0);
        $Ints = array('零' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9, '十' => 10);
        $var = str_replace('零', "", $var);
        foreach ($splits as $key => $step) {
            if (strpos($var, $key)) {
                $strs = explode($key, $var);
                $start += CnToInt(array_shift($strs)) * $step;
                $var = join('', $strs);
            }
        }
        foreach ($chars as $key => $step) {
            if (strpos($var, $key) !== FALSE) {
                $vs = explode($key, $var);
                if ($vs[0] === "") {
                    $vs[0] = '一';
                }
                $start += $Ints[array_shift($vs)] * $step;
                $var = join('', $vs);
            } elseif (mb_strlen($var, 'utf-8') === 1) {
                $start += $Ints[$var];
                $var = '';
                break;
            }
        }
        return $start;
    } else {
        return intval($var);
    }

}

/**
 * 获取客户ip
 * @return string
 */
function getIp()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
        $ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

/**
 * 判断用户来源IP
 * @return bool
 */
function checkIp(){
    $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=';
    $result = json_decode(iCurl($url . getip()), true);
    return $result;
}

function urlType($type=1){
    global $_GPC;
    !empty($_GPC['act'])?$_GPC['act'] = ucfirst($_GPC['act']):$_GPC['act'] = 'Home';
    if(empty($_GPC['name'])){
        $_GPC['name'] = 'index';
    }
    if(empty($_GPC['op'])){
        $_GPC['op'] = 'index';
    }
}
/**
 * @return string
 * 获取当前页面url
 */
function get_page_url(){
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
    return urlencode($url);
}

/**
 * @return string
 * 获取手机类型型号详细信息
 */
function getMobileType(){
    $HTTP_USER_AGENT=$_SERVER['HTTP_USER_AGENT'];
    $iOS=array("iPhone","iPad","iPod","iTouch");
    preg_match_all('/(?<=\()([^\]]*?)(?=\))/',$HTTP_USER_AGENT, $arr);
    $tmp = str_replace("Linux; ","",$arr[0][0]);
    $tmp = str_replace("_",".",$tmp);
    $tmp = str_replace(" CPU iPhone OS ","",$tmp);
    $tmp = str_replace(" like Mac OS X","",$tmp);
    $tmp = str_replace(" Build","",$tmp);
    $osarr=explode(";",$tmp);
    if(in_array($osarr[0],$iOS)){
        $mobile=explode("Mobile/",$HTTP_USER_AGENT);
        $mobile=explode(" ",$mobile[1]);
        $mobiletype="手机：".$osarr[0]."　版本：".$mobile[0]."　系统：iOS".$osarr[1];
    }else{
        $mobile=explode("/",$osarr[1]);
        $mobiletype="手机：".$mobile[0]."　版本：".$mobile[1]."　系统：".$osarr[0];
        #其他系统
        if(str_replace(" ","",$osarr[0])=="U"){
            $tmp = str_replace("Linux; U; ","",$arr[0][0]);
            $tmp = explode(" Build/",$tmp);
            $tmp = explode("; zh-cn; ",$tmp[0]);
            $mobiletype="手机：".$tmp[1]."　系统：".$tmp[0];
        }
    }
    return $mobiletype;
}

/**
 * 将excel转换为数组 by aibhsc
 * */
function format_excel2array($filePath='',$sheet=0){
    if(empty($filePath) or !file_exists($filePath)){die('file not exists');}
    require_once '../source/library/phpexcel/PHPExcel.php';
    $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
    if(!$PHPReader->canRead($filePath)){
        $PHPReader = new \PHPExcel_Reader_Excel5();
        if(!$PHPReader->canRead($filePath)){
            echo 'no Excel';
            return ;
        }
    }
    $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
    $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
    $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
    $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
    $data = array();
    for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
            $addr = $colIndex.$rowIndex;
            $cell = $currentSheet->getCell($addr)->getValue();
            if($cell instanceof \PHPExcel_RichText){ //富文本转换字符串
                $cell = $cell->__toString();
            }
            $data[$rowIndex][$colIndex] = $cell;
        }
    }
    return $data;
}