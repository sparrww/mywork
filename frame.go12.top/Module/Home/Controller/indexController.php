<?php
// 首页

namespace Home\Controller;

use Lib\Controller as Controller;


class indexController extends Controller
{

    /**
     * 首页
     *
     * @return html
     */
    public function index()
    {
        global $_W;
        require_once SYSTEM_CLASS_PATH.'/xiaochengxu/demo.php';

    }

    public function index2()
    {
        setLog(1);

    }

}