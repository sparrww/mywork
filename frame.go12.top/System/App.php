<?php
/*******************************************************************
 * @authors Air
 * @date    2014-09-06
 * @copy    Copyright © 2013-2018 Powered by Air Web Studio  
 *******************************************************************/
// 加载

defined('TPL_INCLUDE') or die( 'Restricted access'); 

if(version_compare(PHP_VERSION,'5.5.0','<'))  die('require PHP > 5.5.0 !');

/*------------------ 系统配置 ------------------*/

	// 项目版本
	define('VERSION', '2.0');


	// 系统定义
	const CONTROLLER 			=   'Controller';	//控制器名称


	// 设置程序运行时间
	ini_set('max_execution_time','120');
	//独立内存使用量
	ini_set("memory_limit","128M");
	// 设置页面时间
	set_time_limit(0);
	// 设置时区
	date_default_timezone_set("PRC");
	// 设置编码
	header("Content-Type:text/html;charset=UTF-8");
	// 脚本运行时间
    define('TIMESTAMP', time());

    $_W = $_GPC = array();

    $_W['isajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $_W['ispost'] = $_SERVER['REQUEST_METHOD'] == 'POST';

/*------------------ 路径定义 ------------------*/

	// 系统路径定义
	define('SYSTEM_PATH',__DIR__);
	define('SYSTEM_LIB_PATH',SYSTEM_PATH.'/Lib');
	define('SYSTEM_COMMON_PATH',SYSTEM_PATH.'/Common');
	define('SYSTEM_DRIVER_PATH',SYSTEM_PATH.'/Driver');
	define('CONFIG_PATH',ROOT_PATH.'/Config');
	define('MODULE_PATH',ROOT_PATH.'/Module');

	// 定义公用路径
	define('PUBLIC_PATH','/Public/');
	define('PCOMMON_PATH',PUBLIC_PATH.'Common/');
    define('UPLOAD_PATH',PUBLIC_PATH.'Upload/');

	
/*------------------ 加载库 ------------------*/


// 加载函数
include_once SYSTEM_COMMON_PATH.'/global.func.php';
include_once SYSTEM_COMMON_PATH.'/pdo.func.php';

// 加载应用函数库
import('Lib.safe');
import('Lib.Lib');
import('Lib.Exceptions');

$_GET = istripslashes($_GET);
$_POST = istripslashes($_POST);
//$_COOKIE = istripslashes($_COOKIE);
$_GPC = array_merge($_GET, $_POST, $_GPC);
$_GPC = ihtmlspecialchars($_GPC);


$_W['config']['db'] =  getconfig('Dbconfig.php');
$_W['config']['setting'] = getconfig('Baseconfig.php');


define('APP_DEBUG', $_W['config']['setting']['debug']);
// 选择DEBUG模式
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

urlType(1);

// 应用路径
defined('ITEM_PATH')			or define('ITEM_PATH',MODULE_PATH.'/'.$_GPC['act']);
defined('CONTROLLER_PATH') 		or define('CONTROLLER_PATH',ITEM_PATH.'/Controller');
defined('VIEW_PATH')			or define('VIEW_PATH',ITEM_PATH.'/View');
defined('DATA_PATH')			or define('DATA_PATH',ITEM_PATH.'/Data');
defined('CACHE_DATA_PATH')		or define('CACHE_DATA_PATH',DATA_PATH.'/Data');
defined('CACHE_VIEW_PATH')		or define('CACHE_VIEW_PATH',DATA_PATH.'/Compile');
defined('ERROR_PATH')			or define('ERROR_PATH',DATA_PATH.'/Error');
defined('LOG_PATH')				or define('LOG_PATH',ERROR_PATH.'/logs');

Lib\Lib::getinstance()->run();
