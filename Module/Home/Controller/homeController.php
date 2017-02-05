<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;

class homeController extends Controller
{


    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        global $_W,$_GPC;
        //路由 实例
        // http://'.$_W['host'].'/index.php?act=home&name=home&op=index

//        pdo_fetch('sql');  //查询一条数据,返回一维数组
//        pdo_fetchall('sql');  //查询多条数据,返回二维数组
//        pdo_fetchcolumn('sql'); //查询字段值,返回字符串
//
//        pdo_insert('table',['数组']); //插入
//        pdo_insertid(); //获取插入后的自增id
//        pdo_update('table',['数组'],['where条件数组']); //更新 返回影响行数
//        pdo_delete('table',['where条件数组']); //删除返回影响行数


        $a = 1111;

        echo $a;die;
        include $this->display('index.html');  //调取模版
    }

}