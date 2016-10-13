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

	// 设置程序运行时间
	ini_set('max_execution_time','0');
	//独立内存使用量
	ini_set("memory_limit","128M");
	// 设置页面时间
	set_time_limit(0);
	// 设置时区
	date_default_timezone_set("PRC");
	// 设置编码
	header("Content-Type:text/html;charset=UTF-8");
	// 脚本开始时间
	define('__BEGIN__', microtime(true));

    define('TIMESTAMP', time());

    $_W = $_GPC = array();
/*------------------ 路径定义 ------------------*/

	// 系统路径定义
	defined('SYSTEM_PATH')			or define('SYSTEM_PATH',__DIR__);
	defined('SYSTEM_LIB_PATH')		or define('SYSTEM_LIB_PATH',SYSTEM_PATH.'/Lib');
	defined('SYSTEM_COMMON_PATH')	or define('SYSTEM_COMMON_PATH',SYSTEM_PATH.'/Common');
	defined('SYSTEM_DRIVER_PATH')	or define('SYSTEM_DRIVER_PATH',SYSTEM_PATH.'/Driver');
	defined('CONFIG_PATH')			or define('CONFIG_PATH',ROOT_PATH.'/Config');
	defined('MODULE_PATH')			or define('MODULE_PATH',ROOT_PATH.'/Module');
    defined('UPLOAD_PATH')			or define('UPLOAD_PATH',ROOT_PATH.'/Upload');

	// 定义公用路径
	define('PUBLIC_PATH','/Public/');

	define('PCOMMON_PATH',PUBLIC_PATH.'Common/');

	
/*------------------ 加载库 ------------------*/


// 加载函数
include_once SYSTEM_COMMON_PATH.'/global.func.php';
include_once SYSTEM_COMMON_PATH.'/pdo.func.php';
include_once SYSTEM_DRIVER_PATH.'/DbPdo.php';

// 加载应用函数库
import('Lib.*');

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
