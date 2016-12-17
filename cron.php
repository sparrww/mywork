<?php
if ( substr ( php_sapi_name () ,  0 ,  3 ) !=  'cgi' ) {
   die();
}
define("TPL_INCLUDE",1);
define('ROOT_PATH',dirname(__FILE__));
// 引入口文件
require ROOT_PATH.'/System/cgi.php';




